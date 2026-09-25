<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\OrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
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
        protected OrderRepository $orderRepository
    ) {}

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

        $modules = self::PLAN_MODULES[$planName] ?? [];
        foreach ($modules as $moduleName) {
            $order->modules()->create([
                'name' => $moduleName,
                'price' => 0,
            ]);
        }

        return $order->load('modules');
    }

    public function syncApprovedShop(Order $order): Shop
    {
        return Shop::withTrashed()->updateOrCreate(
            ['owner_id' => $order->user_id],
            [
                'shop_name' => $order->shop_name,
                'phone' => $order->phone,
                'block_street' => $order->block_street,
                'municipality' => $order->municipality,
                'barangay' => $order->barangay,
                'postal_code' => $order->postal_code,
                'bir_expiry_date' => $order->bir_expiry_date,
                'dti_expiry_date' => $order->dti_expiry_date,
                'mayors_expiry_date' => $order->mayors_expiry_date,
                'sanitary_expiry_date' => $order->sanitary_expiry_date,
                'status' => 'active',
                'deleted_at' => null,
            ],
        );
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
}
