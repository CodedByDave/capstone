<?php

namespace App\Services;

use App\Exceptions\OrderSubmissionException;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use App\Notifications\BusinessAgreementExecutedNotification;
use App\Repositories\OrderRepository;
use App\Repositories\ShopRepository;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public const MAX_RESUBMISSIONS = 3;

    private const PLAN_MODULES = [
        'Basic' => [
            'HRM',
            'Operations',
        ],
        'Standard' => [
            'HRM',
            'Operations',
            'Inventory Management',
            'Finance Management',
        ],
        'Premium' => [
            'HRM',
            'Operations',
            'Inventory Management',
            'Finance Management',
            'Reports & Analytics',
        ],
    ];

    public function __construct(
        protected OrderRepository $orderRepository,
        protected UserRepository $userRepository,
        protected ShopRepository $shopRepository,
        protected BusinessAgreementService $businessAgreementService,
    ) {}

    public function hasOpenApplication(int $userId): bool
    {
        return $this->orderRepository->hasOpenApplication($userId);
    }

    public function checkoutConfirmationData(User $user, array $checkout): array
    {
        $shop = $this->shopRepository->findByOwnerId($user->id);
        $municipalityMap = [
            'Cavite City' => 'City of Cavite',
            'Dasmariñas' => 'City of Dasmariñas',
            'Bacoor' => 'City of Bacoor',
            'Imus' => 'City of Imus',
            'Trece Martires' => 'City of Trece Martires',
            'General Trias' => 'City of General Trias',
        ];
        $municipality = $shop?->municipality ?? '';

        return [
            'planName' => $checkout['plan_name'] ?? 'Standard',
            'billingMonths' => (int) ($checkout['billing_months'] ?? 12),
            'vatPct' => 12,
            'user' => ['name' => $user->name, 'email' => $user->email],
            'shop' => [
                'phone' => $shop?->phone ?? '',
                'shop_name' => $shop?->shop_name ?? '',
                'block_street' => $shop?->block_street ?? '',
                'municipality' => $municipalityMap[$municipality] ?? $municipality,
                'barangay' => $shop?->barangay ?? '',
                'postal_code' => $shop?->postal_code ?? '',
            ],
        ];
    }

    public function submitApplication(
        User $user,
        array $data,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): Order {
        return DB::transaction(function () use ($user, $data, $ipAddress, $userAgent) {
            // Serialize submissions for one owner so concurrent requests cannot
            // create duplicate pending applications or bypass the retry cap.
            $this->userRepository->lockForOrderSubmission($user->id);

            if ($this->orderRepository->hasOpenApplication($user->id)) {
                throw new OrderSubmissionException('You already have an active plan or pending order.');
            }

            if (! $this->resubmissionStatus($user->id)['can_resubmit']) {
                throw new OrderSubmissionException(
                    'You have reached the maximum of '.self::MAX_RESUBMISSIONS.' order resubmissions. Please contact support for assistance.'
                );
            }

            $planName = $data['plan_name'];
            $billingMonths = (int) $data['billing_months'];
            $grandTotal = $this->calculateGrandTotal($planName, $billingMonths);

            $order = $this->create([
                ...$data,
                'owner_name' => $user->name,
                'email' => $user->email,
                'user_id' => $user->id,
                'total_price' => $grandTotal,
            ]);

            $kycPaths = [];
            foreach (['kyc_bir', 'kyc_dti', 'kyc_mayors', 'kyc_sanitary'] as $key) {
                if (! empty($data[$key])) {
                    $kycPaths[$key] = $data[$key]->store("kyc/{$order->id}", 'private');
                }
            }

            if ($kycPaths !== []) {
                $this->orderRepository->updateKycDocuments($order, $kycPaths);
            }

            $this->businessAgreementService->acceptForOrder(
                $user,
                $order,
                $data,
                $ipAddress,
                $userAgent,
            );

            return $order->refresh()->load('modules');
        });
    }

    public function resubmissionStatus(int $userId): array
    {
        $rejectedApplications = $this->orderRepository->countRejectedApplications($userId);
        $used = min(self::MAX_RESUBMISSIONS, max(0, $rejectedApplications - 1));

        return [
            'used' => $used,
            'max' => self::MAX_RESUBMISSIONS,
            'remaining' => self::MAX_RESUBMISSIONS - $used,
            // One initial submission plus three resubmissions are allowed.
            'can_resubmit' => $rejectedApplications <= self::MAX_RESUBMISSIONS,
        ];
    }

    private function calculateGrandTotal(string $planName, int $billingMonths): float
    {
        $planPrices = ['Basic' => 3800, 'Standard' => 6300, 'Premium' => 8000];
        $discounts = [1 => 0, 12 => 10, 24 => 20, 48 => 30];
        $discountPct = $discounts[$billingMonths] ?? 0;
        $subtotal = $planPrices[$planName] * (1 - $discountPct / 100) * $billingMonths;

        return round($subtotal + ($subtotal * 0.12));
    }

    public function create(array $data): Order
    {
        $planName = $data['plan_name'];
        $billingMonths = (int) $data['billing_months'];
        $subscriptionPlan = $billingMonths === 1 ? 'monthly' : 'annually';

        $order = $this->orderRepository->create([
            'user_id' => $data['user_id'],
            'shop_name' => $data['shop_name'],
            'owner_name' => $data['owner_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'block_street' => $data['block_street'] ?? null,
            'municipality' => $data['municipality'],
            'barangay' => $data['barangay'],
            'postal_code' => $data['postal_code'],
            'bir_expiry_date' => $data['bir_expiry_date'] ?? null,
            'dti_expiry_date' => $data['dti_expiry_date'] ?? null,
            'mayors_expiry_date' => $data['mayors_expiry_date'] ?? null,
            'sanitary_expiry_date' => $data['sanitary_expiry_date'] ?? null,
            'total_price' => $data['total_price'],
            'plan_name' => $data['plan_name'],
            'billing_months' => $data['billing_months'],
            'status' => 'pending',
            'subscription_plan' => $subscriptionPlan,
            // Paid subscription time starts after the payment is verified.
            'expires_at' => null,
            'payment_method' => $data['payment_method'] ?? null,
        ]);

        $this->orderRepository->addModules($order, self::PLAN_MODULES[$planName] ?? []);

        return $order->load('modules');
    }

    public function syncApprovedShop(Order $order): Shop
    {
        return $this->shopRepository->syncFromApprovedOrder($order);
    }

    public function approveWithPlatformSignature(
        User $admin,
        Order $order,
        array $data,
        ?string $ipAddress,
        ?string $userAgent,
    ): Order {
        [$approvedOrder, $acceptance] = DB::transaction(function () use ($admin, $order, $data, $ipAddress, $userAgent) {
            $lockedOrder = $this->orderRepository->findForApproval($order->id);

            if ($lockedOrder->status !== 'pending') {
                throw ValidationException::withMessages([
                    'order' => 'Only pending orders can be approved.',
                ]);
            }

            $acceptance = $this->businessAgreementService->countersignForOrder(
                $admin,
                $lockedOrder,
                $data,
                $ipAddress,
                $userAgent,
            );

            $this->orderRepository->markApproved($lockedOrder);

            return [$this->orderRepository->refresh($lockedOrder), $acceptance];
        });

        $approvedOrder->user?->notify(new BusinessAgreementExecutedNotification($acceptance));

        return $approvedOrder;
    }

    public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->orderRepository->getPaginated($filters, $perPage);
    }

    public function getStats(): array
    {
        return $this->orderRepository->getStats();
    }

    public function getForCsvExport(array $filters = []): Collection
    {
        return $this->orderRepository->getForCsvExport($filters);
    }

    public function importCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            throw ValidationException::withMessages([
                'file' => 'The CSV file could not be opened.',
            ]);
        }

        try {
            $header = fgetcsv($handle, 0, ',', '"', '');

            if ($header === false) {
                throw ValidationException::withMessages([
                    'file' => 'The CSV file is empty.',
                ]);
            }

            $header = array_map(function ($column) {
                $column = preg_replace('/^\xEF\xBB\xBF/', '', (string) $column);

                return Str::snake(trim($column));
            }, $header);

            $required = [
                'transaction_reference', 'owner_email', 'shop_name',
                'owner_name', 'phone', 'municipality', 'barangay', 'plan_name',
                'billing_months', 'total_price', 'status',
            ];
            $missing = array_diff($required, $header);

            if ($missing !== []) {
                throw ValidationException::withMessages([
                    'file' => 'Missing required columns: '.implode(', ', $missing).'.',
                ]);
            }

            $rows = [];
            $rowNumber = 1;

            while (($values = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                $rowNumber++;

                if (count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
                    continue;
                }

                $values = array_pad($values, count($header), null);
                $row = array_combine($header, array_slice($values, 0, count($header)));
                $status = strtolower(trim((string) ($row['status'] ?? '')));
                $plan = ucfirst(strtolower(trim((string) ($row['plan_name'] ?? ''))));
                $billingMonths = (int) ($row['billing_months'] ?? 0);
                $totalPrice = $row['total_price'] ?? null;
                $expiresAt = trim((string) ($row['expires_at'] ?? ''));
                $orderedAt = trim((string) ($row['ordered_at'] ?? ''));

                if (! filter_var($row['owner_email'] ?? null, FILTER_VALIDATE_EMAIL)) {
                    throw ValidationException::withMessages([
                        'file' => "Row {$rowNumber} contains an invalid owner_email.",
                    ]);
                }

                foreach (['transaction_reference', 'shop_name', 'owner_name', 'phone', 'municipality', 'barangay'] as $field) {
                    if (trim((string) ($row[$field] ?? '')) === '') {
                        throw ValidationException::withMessages([
                            'file' => "Row {$rowNumber} is missing {$field}.",
                        ]);
                    }
                }

                if (! in_array($status, ['pending', 'approved', 'paid', 'rejected', 'failed', 'expired'], true)) {
                    throw ValidationException::withMessages([
                        'file' => "Row {$rowNumber} contains an invalid status.",
                    ]);
                }

                if (! array_key_exists($plan, self::PLAN_MODULES)) {
                    throw ValidationException::withMessages([
                        'file' => "Row {$rowNumber} contains an invalid plan_name.",
                    ]);
                }

                if (! in_array($billingMonths, [1, 12], true) || ! is_numeric($totalPrice) || (float) $totalPrice < 0) {
                    throw ValidationException::withMessages([
                        'file' => "Row {$rowNumber} contains invalid billing or price data.",
                    ]);
                }

                if (($expiresAt !== '' && strtotime($expiresAt) === false)
                    || ($orderedAt !== '' && strtotime($orderedAt) === false)) {
                    throw ValidationException::withMessages([
                        'file' => "Row {$rowNumber} contains an invalid date.",
                    ]);
                }

                $rows[] = [
                    'transaction_reference' => trim((string) $row['transaction_reference']),
                    'owner_email' => strtolower(trim((string) $row['owner_email'])),
                    'shop_name' => trim((string) $row['shop_name']),
                    'owner_name' => trim((string) $row['owner_name']),
                    'phone' => trim((string) $row['phone']),
                    'block_street' => trim((string) ($row['block_street'] ?? '')) ?: null,
                    'municipality' => trim((string) $row['municipality']),
                    'barangay' => trim((string) $row['barangay']),
                    'postal_code' => trim((string) ($row['postal_code'] ?? '')) ?: null,
                    'plan_name' => $plan,
                    'billing_months' => $billingMonths,
                    'total_price' => (float) $totalPrice,
                    'payment_method' => trim((string) ($row['payment_method'] ?? '')) ?: null,
                    'status' => $status,
                    'expires_at' => $expiresAt ?: null,
                    'is_upgrade' => in_array(strtolower(trim((string) ($row['is_upgrade'] ?? ''))), ['1', 'yes', 'true'], true),
                    'is_trial' => in_array(strtolower(trim((string) ($row['is_trial'] ?? ''))), ['1', 'yes', 'true'], true),
                    'ordered_at' => $orderedAt ?: null,
                ];
            }
        } finally {
            fclose($handle);
        }

        return DB::transaction(function () use ($rows) {
            $created = 0;
            $updated = 0;
            $users = User::whereIn('email', collect($rows)->pluck('owner_email')->unique())
                ->get()
                ->keyBy(fn (User $user) => strtolower($user->email));

            foreach ($rows as $row) {
                $user = $users->get($row['owner_email']);

                if ($user === null) {
                    throw ValidationException::withMessages([
                        'file' => "No user exists for {$row['owner_email']}.",
                    ]);
                }

                $order = Order::firstOrNew([
                    'transaction_reference' => $row['transaction_reference'],
                ]);
                $order->exists ? $updated++ : $created++;

                $order->fill(collect($row)->except([
                    'transaction_reference', 'owner_email', 'ordered_at',
                ])->all());
                $order->transaction_reference = $row['transaction_reference'];
                $order->user_id = $user->id;
                $order->email = $row['owner_email'];
                if ($row['ordered_at'] !== null) {
                    $order->created_at = $row['ordered_at'];
                }
                $order->save();

                $order->modules()->delete();
                foreach (self::PLAN_MODULES[$row['plan_name']] as $moduleName) {
                    $order->modules()->create(['name' => $moduleName, 'price' => 0]);
                }
            }

            return compact('created', 'updated');
        });
    }

    public function find(int $id): Order
    {
        return $this->orderRepository->findWithRelations($id);
    }

    public function adminDetails(Order $order): Order
    {
        return $this->orderRepository->loadAdminDetails($order);
    }
}
