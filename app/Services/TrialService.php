<?php

namespace App\Services;

use App\Exceptions\OrderSubmissionException;
use App\Models\Order;
use App\Models\User;
use App\Repositories\ShopRepository;
use App\Repositories\TrialRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;

class TrialService
{
    public const TRIAL_DAYS = 7;

    private const TRIAL_PLAN = 'Standard';

    private const TRIAL_MODULES = [
        'HRM',
        'Operations',
        'Inventory Management',
        'Finance Management',
    ];

    public function __construct(
        private readonly TrialRepository $trialRepository,
        private readonly ShopRepository $shopRepository,
        private readonly UserRepository $userRepository,
        private readonly BusinessAgreementService $agreementService,
    ) {}

    public function pageData(User $user): array
    {
        $shop = $this->shopRepository->findByOwnerId($user->id);

        return [
            'trialDays' => self::TRIAL_DAYS,
            'user' => ['name' => $user->name, 'email' => $user->email],
            'shop' => [
                'shop_name' => $shop?->shop_name ?? '',
                'phone' => $shop?->phone ?? '',
                'block_street' => $shop?->block_street ?? '',
                'municipality' => $shop?->municipality ?? '',
                'barangay' => $shop?->barangay ?? '',
                'postal_code' => $shop?->postal_code ?? '',
            ],
            'agreement' => $this->agreementService->currentAgreementData($user),
        ];
    }

    public function availability(User $user): array
    {
        return [
            'has_open_application' => $this->trialRepository->hasOpenApplication($user->id),
            'has_used_trial' => $this->trialRepository->hasUsedTrial($user->id),
        ];
    }

    public function start(
        User $user,
        array $data,
        ?string $ipAddress,
        ?string $userAgent,
    ): Order {
        return DB::transaction(function () use ($user, $data, $ipAddress, $userAgent) {
            $this->userRepository->lockForOrderSubmission($user->id);
            $availability = $this->availability($user);

            if ($availability['has_open_application']) {
                throw new OrderSubmissionException('You already have an active plan or pending order.');
            }

            if ($availability['has_used_trial']) {
                throw new OrderSubmissionException('You have already used your free trial. Choose a plan to continue.');
            }

            $order = $this->trialRepository->create([
                'user_id' => $user->id,
                'shop_name' => $data['shop_name'],
                'owner_name' => $user->name,
                'email' => $user->email,
                'phone' => $data['phone'],
                'block_street' => $data['block_street'] ?? null,
                'municipality' => $data['municipality'],
                'barangay' => $data['barangay'],
                'postal_code' => $data['postal_code'],
                'plan_name' => self::TRIAL_PLAN,
                'billing_months' => 0,
                'total_price' => 0,
                'status' => 'approved',
                'is_trial' => true,
                'payment_method' => null,
                'expires_at' => now()->addDays(self::TRIAL_DAYS),
            ], self::TRIAL_MODULES);

            $this->shopRepository->activateForTrial($user->id, $data);
            $this->agreementService->acceptForOrder(
                $user,
                $order,
                $data,
                $ipAddress,
                $userAgent,
            );

            return $order;
        });
    }
}
