<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Models\BusinessAgreement;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Whole onboarding KYC flow over HTTP: scan -> submit -> admin reject with
 * flags -> resubmission keeps the unflagged items.
 */
class KycOnboardingFlowTest extends TestCase
{
    use RefreshDatabase;

    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    private User $owner;

    private BusinessAgreement $agreement;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');
        Storage::fake('local');
        Storage::fake('public');
        config(['services.ocr_space.key' => 'test-key']);

        $this->owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
        Shop::create([
            'owner_id' => $this->owner->id,
            'shop_name' => 'Suds & Bubbles Laundry',
            'phone' => '09171234567',
            'block_street' => '12 Rizal St',
            'municipality' => 'Imus',
            'barangay' => 'Poblacion',
            'postal_code' => '4103',
        ]);
        $this->agreement = BusinessAgreement::create([
            'title' => 'Business Agreement',
            'version' => '1.0',
            'content' => 'Terms',
            'content_hash' => hash('sha256', 'Terms'),
            'effective_at' => now()->subDay(),
            'published_at' => now()->subDay(),
            'is_active' => true,
        ]);
    }

    private function png(string $name, string $salt = ''): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode(self::PNG).$salt);
    }

    private function fakeOcr(string $expiry): void
    {
        Http::fake(['api.ocr.space/*' => Http::response([
            'IsErroredOnProcessing' => false,
            'ParsedResults' => [['ParsedText' => "TIN: 123-456-789-000\nRegistered Name: SUDS AND BUBBLES LAUNDRY\nRegistered Address: 12 Rizal St, Poblacion, Imus, Cavite\nValid until: {$expiry}"]],
        ])]);
    }

    private function submission(array $overrides = []): array
    {
        return $overrides + [
            'plan_name' => 'Standard',
            'billing_months' => 12,
            'shop_name' => 'Suds & Bubbles Laundry',
            'email' => $this->owner->email,
            'phone' => '09171234567',
            'block_street' => '12 Rizal St',
            'municipality' => 'Imus',
            'barangay' => 'Poblacion',
            'postal_code' => '4103',
            'kyc_bir' => $this->png('bir.png', 'bir'),
            'kyc_dti' => $this->png('dti.png', 'dti'),
            'kyc_mayors' => $this->png('mayors.png', 'mayors'),
            'bir_expiry_date' => now()->addYear()->toDateString(),
            'dti_expiry_date' => now()->addYear()->toDateString(),
            'mayors_expiry_date' => now()->addYear()->toDateString(),
            'kyc_confirmed' => [
                'tin' => '123-456-789-000',
                'registered_name' => 'SUDS AND BUBBLES LAUNDRY',
                'registered_address' => '12 Rizal St, Poblacion, Imus, Cavite',
            ],
            'agreement_public_id' => $this->agreement->public_id,
            'signer_name' => 'Juan Dela Cruz',
            'signer_role' => 'Owner',
            'signer_authority_confirmed' => '1',
            'agreement_accepted' => '1',
            'signature_method' => 'uploaded',
            'signature_image' => $this->png('sig.png', 'sig'),
        ];
    }

    public function test_full_flow_scan_submit_reject_with_flags_and_resubmit(): void
    {
        $this->fakeOcr($expiry = now()->addMonths(6)->toDateString());

        // 1. Upload -> OCR scan returns the fields for the review form.
        $this->actingAs($this->owner)
            ->postJson('/kyc/scan-expiry', ['document' => $this->png('bir.png', 'bir')])
            ->assertOk()
            ->assertJson(['tin' => '123-456-789-000', 'registered_name' => 'SUDS AND BUBBLES LAUNDRY', 'expiry_date' => $expiry]);
        $this->postJson('/kyc/scan-expiry', ['document' => $this->png('dti.png', 'dti')])->assertOk();

        // 2. Submit with a BIR expiry date that differs from what OCR read.
        $this->post('/checkout/process', $this->submission())->assertSessionHasNoErrors();

        $order = Order::where('user_id', $this->owner->id)->firstOrFail();
        $this->assertSame('pending', $order->status);
        $this->assertSame('123-456-789-000', $order->kyc_ocr_data['bir']['tin']);
        $this->assertSame($expiry, $order->kyc_ocr_data['bir']['expiry_date']);
        $this->assertSame('SUDS AND BUBBLES LAUNDRY', $order->kyc_confirmed_data['registered_name']);
        // The mayor's permit was never scanned, so nothing is claimed about it.
        $this->assertArrayNotHasKey('mayors', $order->kyc_ocr_data);

        // 3. Admin sees the mismatch and rejects, flagging only the BIR expiry date.
        $admin = User::factory()->create(['role' => AccountType::SuperAdmin->value]);
        $this->actingAs($admin)
            ->get("/admin/orders/{$order->public_id}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/orders/Show')
                ->where('kycReview', fn ($rows) => collect($rows)->firstWhere('key', 'bir_expiry_date')['status'] === 'mismatch'));

        $this->post("/admin/orders/{$order->public_id}/reject", [
            'rejection_reason' => 'BIR expiry date does not match the document.',
            'flags' => ['bir_expiry_date'],
        ])->assertSessionHasNoErrors();

        $order->refresh();
        $this->assertSame('rejected', $order->status);
        $this->assertSame(['bir_expiry_date'], $order->kyc_flags);

        // 4. Unverified owners are sent to the onboarding verification, not the checkout form.
        $this->actingAs($this->owner)
            ->withSession(['checkout' => ['plan_name' => 'Standard', 'billing_months' => 12]])
            ->get('/checkout/confirm')
            ->assertRedirect(route('shop.dashboard'));

        // 5. A direct resubmission still carries over the unflagged documents anything: documents are carried over.
        $resubmit = $this->submission([
            'kyc_bir' => null, 'kyc_dti' => null, 'kyc_mayors' => null,
            'reuse_kyc' => ['bir', 'dti', 'mayors'],
            'bir_expiry_date' => $expiry,
        ]);
        unset($resubmit['kyc_bir'], $resubmit['kyc_dti'], $resubmit['kyc_mayors']);
        $this->post('/checkout/process', $resubmit)->assertSessionHasNoErrors();

        $second = Order::where('user_id', $this->owner->id)->where('status', 'pending')->firstOrFail();
        $this->assertNotSame($order->id, $second->id);
        foreach (['kyc_bir', 'kyc_dti', 'kyc_mayors'] as $column) {
            Storage::disk('private')->assertExists($second->{$column});
            $this->assertStringStartsWith("kyc/{$second->id}/", $second->{$column});
        }
        $this->assertSame('123-456-789-000', $second->kyc_ocr_data['bir']['tin']);
    }

    public function test_flagged_document_cannot_be_reused(): void
    {
        $this->fakeOcr(now()->addYear()->toDateString());
        $this->actingAs($this->owner);
        $this->postJson('/kyc/scan-expiry', ['document' => $this->png('bir.png', 'bir')]);
        $this->post('/checkout/process', $this->submission());

        $order = Order::where('user_id', $this->owner->id)->firstOrFail();
        $admin = User::factory()->create(['role' => AccountType::SuperAdmin->value]);
        $this->actingAs($admin)->post("/admin/orders/{$order->public_id}/reject", [
            'rejection_reason' => 'BIR document is blurry.',
            'flags' => ['kyc_bir'],
        ]);

        $resubmit = $this->submission(['reuse_kyc' => ['bir', 'dti', 'mayors']]);
        unset($resubmit['kyc_bir'], $resubmit['kyc_dti'], $resubmit['kyc_mayors']);
        $this->actingAs($this->owner)->post('/checkout/process', $resubmit);

        $this->assertSame(1, Order::where('user_id', $this->owner->id)->count());
    }

    public function test_confirmed_kyc_details_are_required(): void
    {
        $data = $this->submission();
        unset($data['kyc_confirmed']);

        $this->actingAs($this->owner)
            ->post('/checkout/process', $data)
            ->assertSessionHasErrors('kyc_confirmed');
    }

    public function test_admin_cannot_flag_unknown_fields(): void
    {
        $order = Order::create([
            'user_id' => $this->owner->id, 'shop_name' => 'X', 'owner_name' => 'X', 'email' => 'x@x.test',
            'phone' => '09171234567', 'municipality' => 'Imus', 'barangay' => 'P', 'postal_code' => '4103',
            'status' => 'pending', 'plan_name' => 'Basic', 'billing_months' => 1, 'total_price' => 1,
        ]);
        $admin = User::factory()->create(['role' => AccountType::SuperAdmin->value]);

        $this->actingAs($admin)
            ->post("/admin/orders/{$order->public_id}/reject", ['rejection_reason' => 'No.', 'flags' => ['password']])
            ->assertSessionHasErrors('flags.0');
    }
}
