<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Models\BusinessAgreement;
use App\Models\KycVerification;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Onboarding: owner uploads KYC, OCR reads it, owner confirms, admin reviews,
 * and the free trial starts automatically on approval.
 */
class KycVerificationFlowTest extends TestCase
{
    use RefreshDatabase;

    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    private User $owner;

    private User $admin;

    private BusinessAgreement $agreement;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');
        Storage::fake('local');
        Storage::fake('public');
        Notification::fake();
        config(['services.ocr_space.key' => 'test-key']);

        $this->owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
        $this->admin = User::factory()->create(['role' => AccountType::SuperAdmin->value]);
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
            'title' => 'Business Agreement', 'version' => '1.0', 'content' => 'Terms',
            'content_hash' => hash('sha256', 'Terms'),
            'effective_at' => now()->subDay(), 'published_at' => now()->subDay(), 'is_active' => true,
        ]);
    }

    private function png(string $name, string $salt): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode(self::PNG).$salt);
    }

    private function fakeOcr(string $expiry): void
    {
        Http::fake(['api.ocr.space/*' => Http::response([
            'IsErroredOnProcessing' => false,
            'ParsedResults' => [['ParsedText' => "TIN: 123-456-789-000\nTax Type: Percentage Tax\nRegistered Name: SUDS AND BUBBLES LAUNDRY\nRegistered Address: 12 Rizal St, Poblacion, Imus, Cavite\nValid until: {$expiry}"]],
        ])]);
    }

    private function submission(array $overrides = []): array
    {
        $year = now()->addYear()->toDateString();

        return $overrides + [
            'kyc_bir' => $this->png('bir.png', 'bir'),
            'kyc_dti' => $this->png('dti.png', 'dti'),
            'kyc_mayors' => $this->png('mayors.png', 'mayors'),
            'bir_expiry_date' => $year,
            'dti_expiry_date' => $year,
            'mayors_expiry_date' => $year,
            'kyc_confirmed' => [
                'tin' => '123-456-789-000',
                'tax_type' => 'non_vat',
                'registered_name' => 'SUDS AND BUBBLES LAUNDRY',
                'registered_address' => '12 Rizal St, Poblacion, Imus, Cavite',
            ],
            'agreement_public_id' => $this->agreement->public_id,
            'agreement_accepted' => '1',
        ];
    }

    private function submitVerification(array $overrides = []): KycVerification
    {
        $this->actingAs($this->owner)
            ->post('/kyc-verification', $this->submission($overrides))
            ->assertSessionHasNoErrors();

        return KycVerification::where('user_id', $this->owner->id)->latest('id')->firstOrFail();
    }

    /**
     * An application whose every detail matches: OCR reads each document, and the owner confirmed
     * the same TIN, tax type, name, address and expiry dates. Only this can be approved.
     */
    private function matchingVerification(): KycVerification
    {
        $this->fakeOcr(now()->addYear()->toDateString());

        foreach (['bir', 'dti', 'mayors'] as $doc) {
            $this->actingAs($this->owner)
                ->postJson('/kyc/scan-expiry', ['document' => $this->png("{$doc}.png", $doc)])
                ->assertOk();
        }

        return $this->submitVerification();
    }

    public function test_dashboard_offers_the_verification_form_to_unverified_owners(): void
    {
        $this->actingAs($this->owner)
            ->get('/shop/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('kyc.status', 'none')
                ->where('kyc.verified', false)
                ->where('kyc.registration.shop_name', 'Suds & Bubbles Laundry'));
    }

    public function test_submission_stores_ocr_result_and_waits_for_review(): void
    {
        $this->fakeOcr($expiry = now()->addMonths(6)->toDateString());
        $this->actingAs($this->owner)
            ->postJson('/kyc/scan-expiry', ['document' => $this->png('bir.png', 'bir')])
            ->assertOk();

        $verification = $this->submitVerification();

        $this->assertSame('pending', $verification->status);
        $this->assertSame('123-456-789-000', $verification->kyc_ocr_data['bir']['tin']);
        $this->assertSame($expiry, $verification->kyc_ocr_data['bir']['expiry_date']);
        $this->assertSame('Suds & Bubbles Laundry', $verification->shop_name);
        $this->assertSame('non_vat', $verification->kyc_ocr_data['bir']['tax_type']);
        $this->assertSame('non_vat', $verification->kyc_confirmed_data['tax_type']);
        $this->assertSame(0, Order::where('user_id', $this->owner->id)->count(), 'No trial until approval.');

        $this->actingAs($this->owner)->get('/shop/dashboard')
            ->assertInertia(fn ($page) => $page->where('kyc.status', 'pending'));
    }

    public function test_unverified_owner_cannot_start_a_trial_or_open_checkout(): void
    {
        $this->actingAs($this->owner)->get('/trial')->assertRedirect(route('shop.dashboard'));

        $this->post('/trial', [
            'shop_name' => 'Suds & Bubbles Laundry', 'phone' => '09171234567', 'municipality' => 'Imus',
            'barangay' => 'Poblacion', 'postal_code' => '4103',
            'agreement_public_id' => $this->agreement->public_id, 'signer_name' => 'Juan', 'signer_role' => 'Owner',
            'signer_authority_confirmed' => '1', 'agreement_accepted' => '1',
            'signature_method' => 'uploaded', 'signature_image' => $this->png('sig.png', 'sig'),
        ]);
        $this->assertSame(0, Order::where('user_id', $this->owner->id)->count());

        $this->withSession(['checkout' => ['plan_name' => 'Basic', 'billing_months' => 1]])
            ->get('/checkout/confirm')->assertRedirect(route('shop.dashboard'));
    }

    public function test_a_mismatched_detail_blocks_approval(): void
    {
        $this->fakeOcr(now()->addMonths(6)->toDateString());
        $this->actingAs($this->owner)->postJson('/kyc/scan-expiry', ['document' => $this->png('bir.png', 'bir')]);
        $verification = $this->submitVerification();

        $this->actingAs($this->admin)
            ->get("/admin/kyc-verifications/{$verification->public_id}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/kyc/Show')
                ->where('kycReview', fn ($rows) => collect($rows)->firstWhere('key', 'bir_expiry_date')['status'] === 'mismatch')
                ->where('approvalBlockers', fn ($blockers) => collect($blockers)->contains('BIR expiry date does not match the document.')));

        $this->post("/admin/kyc-verifications/{$verification->public_id}/approve")
            ->assertSessionHasErrors('verification');

        $this->assertSame('pending', $verification->refresh()->status);
        $this->assertSame(0, Order::where('user_id', $this->owner->id)->count(), 'No trial without approval.');
    }

    public function test_a_detail_ocr_could_not_read_blocks_approval(): void
    {
        // The DTI and Mayor's permits were never read, so their expiry dates are unconfirmed.
        $this->fakeOcr(now()->addYear()->toDateString());
        $this->actingAs($this->owner)->postJson('/kyc/scan-expiry', ['document' => $this->png('bir.png', 'bir')]);
        $verification = $this->submitVerification();

        $this->actingAs($this->admin)
            ->post("/admin/kyc-verifications/{$verification->public_id}/approve")
            ->assertSessionHasErrors('verification');

        $this->assertSame('pending', $verification->refresh()->status);
    }

    public function test_an_application_with_nothing_read_cannot_be_approved(): void
    {
        $verification = $this->submitVerification();

        $this->actingAs($this->admin)
            ->post("/admin/kyc-verifications/{$verification->public_id}/approve")
            ->assertSessionHasErrors('verification');

        $this->assertSame('pending', $verification->refresh()->status);
    }

    public function test_a_fully_matching_application_is_approved_and_starts_the_free_trial(): void
    {
        $verification = $this->matchingVerification();

        $this->actingAs($this->admin)
            ->get("/admin/kyc-verifications/{$verification->public_id}")
            ->assertInertia(fn ($page) => $page
                ->where('approvalBlockers', [])
                ->where('kycReview', fn ($rows) => collect($rows)->every(fn ($row) => $row['status'] === 'match')));

        $this->post("/admin/kyc-verifications/{$verification->public_id}/approve")->assertSessionHasNoErrors();

        $this->assertSame('approved', $verification->refresh()->status);
        $this->assertSame($this->admin->id, $verification->reviewed_by);

        $trial = Order::where('user_id', $this->owner->id)->firstOrFail();
        $this->assertTrue($trial->is_trial);
        $this->assertSame('approved', $trial->status);
        $this->assertTrue($trial->expires_at->isFuture());

        $shop = Shop::where('owner_id', $this->owner->id)->firstOrFail();
        $this->assertSame($verification->bir_expiry_date->toDateString(), $shop->bir_expiry_date->toDateString());
        $this->assertSame('non_vat', $shop->tax_type);
    }

    public function test_rejection_keeps_unflagged_items_for_the_resubmission(): void
    {
        $verification = $this->submitVerification();

        $this->actingAs($this->admin)->post("/admin/kyc-verifications/{$verification->public_id}/reject", [
            'rejection_reason' => 'BIR expiry date does not match the document.',
            'flags' => ['bir_expiry_date'],
        ])->assertSessionHasNoErrors();

        $this->assertSame('rejected', $verification->refresh()->status);
        $this->assertSame(0, Order::where('user_id', $this->owner->id)->count(), 'No trial after rejection.');

        $this->actingAs($this->owner)->get('/shop/dashboard')
            ->assertInertia(fn ($page) => $page
                ->where('kyc.status', 'rejected')
                ->where('kyc.previous.reason', 'BIR expiry date does not match the document.')
                ->where('kyc.previous.flags', ['bir_expiry_date'])
                ->where('kyc.previous.reusable_docs', ['bir', 'dti', 'mayors'])
                ->missing('kyc.previous.expiry.bir')
                ->has('kyc.previous.expiry.dti')
                ->where('kyc.previous.confirmed.tin', '123-456-789-000'));

        $resubmit = $this->submission(['reuse_kyc' => ['bir', 'dti', 'mayors']]);
        unset($resubmit['kyc_bir'], $resubmit['kyc_dti'], $resubmit['kyc_mayors']);
        $this->post('/kyc-verification', $resubmit)->assertSessionHasNoErrors();

        $second = KycVerification::where('user_id', $this->owner->id)->where('status', 'pending')->firstOrFail();
        foreach (['kyc_bir', 'kyc_dti', 'kyc_mayors'] as $column) {
            Storage::disk('private')->assertExists($second->{$column});
        }
    }

    public function test_a_flagged_document_cannot_be_reused(): void
    {
        $verification = $this->submitVerification();
        $this->actingAs($this->admin)->post("/admin/kyc-verifications/{$verification->public_id}/reject", [
            'rejection_reason' => 'Blurry BIR.', 'flags' => ['kyc_bir'],
        ]);

        $resubmit = $this->submission(['reuse_kyc' => ['bir', 'dti', 'mayors']]);
        unset($resubmit['kyc_bir'], $resubmit['kyc_dti'], $resubmit['kyc_mayors']);
        $this->actingAs($this->owner)->post('/kyc-verification', $resubmit);

        $this->assertSame(1, KycVerification::where('user_id', $this->owner->id)->count());
    }

    public function test_a_decided_verification_cannot_be_decided_again(): void
    {
        $verification = $this->matchingVerification();
        $this->actingAs($this->admin)->post("/admin/kyc-verifications/{$verification->public_id}/approve");

        $this->post("/admin/kyc-verifications/{$verification->public_id}/reject", ['rejection_reason' => 'Late.'])
            ->assertSessionHasErrors('verification');
        $this->assertSame('approved', $verification->refresh()->status);
    }

    public function test_a_trial_owner_buys_a_plan_without_documents_or_admin_review(): void
    {
        $verification = $this->matchingVerification();
        $this->actingAs($this->admin)->post("/admin/kyc-verifications/{$verification->public_id}/approve");

        // Approval started the free trial on its own.
        $this->assertTrue(Order::where('user_id', $this->owner->id)->where('is_trial', true)->where('status', 'approved')->exists());

        $this->mock(\App\Services\PaymongoService::class)
            ->shouldReceive('createCheckoutSession')
            ->once()
            ->andReturn(['data' => ['id' => 'cs_test_upgrade', 'attributes' => ['checkout_url' => 'https://checkout.paymongo.test/upgrade']]]);

        // Only the plan and the payment method: no documents, no expiry dates.
        $this->actingAs($this->owner)
            ->post('/shop/upgrade/process', ['plan_name' => 'Basic', 'billing_months' => 1, 'payment_method' => 'gcash'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('shop.upgrade.confirm'));

        // The plan order waits for the payment, not for an admin, and is on record in Order Management.
        $order = Order::where('user_id', $this->owner->id)->where('is_trial', false)->firstOrFail();
        $this->assertSame('Basic', $order->plan_name);
        $this->assertTrue((bool) $order->is_upgrade);
        $this->assertNull($order->kyc_bir);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'paymongo_session_id' => 'cs_test_upgrade']);
    }

    public function test_the_old_checkout_application_is_replaced_by_upgrade_plan(): void
    {
        $this->actingAs($this->owner)
            ->post('/checkout/process', ['plan_name' => 'Basic', 'billing_months' => 1])
            ->assertRedirect(route('shop.upgrade'));

        $this->assertSame(0, Order::where('user_id', $this->owner->id)->where('status', 'pending')->count());
    }

    public function test_the_agreement_is_accepted_without_a_signature_image(): void
    {
        $this->submitVerification();

        $acceptance = \App\Models\BusinessAgreementAcceptance::firstOrFail();
        $this->assertSame($this->owner->name, $acceptance->signer_name);
        $this->assertSame('Owner', $acceptance->signer_role);
        $this->assertNull($acceptance->signature_path);

        $this->actingAs($this->owner)->post('/business-agreement/preview', [])
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_admin_shop_page_shows_the_submitted_kyc_documents_and_dates(): void
    {
        $verification = $this->submitVerification();
        $shop = Shop::where('owner_id', $this->owner->id)->firstOrFail();

        $this->actingAs($this->admin)
            ->get("/admin/shop/{$shop->public_id}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/shop/Show')
                ->where('shop.permit_files.bir', $verification->kyc_bir)
                ->where('shop.permit_files.dti', $verification->kyc_dti)
                ->where('shop.permit_files.mayors', $verification->kyc_mayors)
                ->where('shop.permit_submission.status', 'pending')
                ->where('shop.bir_expiry_date', $verification->bir_expiry_date->toDateString()));
    }

    public function test_tax_type_is_required_and_must_be_vat_or_non_vat(): void
    {
        $data = $this->submission();
        unset($data['kyc_confirmed']['tax_type']);
        $this->actingAs($this->owner)->post('/kyc-verification', $data)->assertSessionHasErrors('kyc_confirmed.tax_type');

        $data['kyc_confirmed']['tax_type'] = 'exempt';
        $this->post('/kyc-verification', $data)->assertSessionHasErrors('kyc_confirmed.tax_type');
        $this->assertSame(0, KycVerification::count());
    }

    public function test_admin_can_flag_a_wrong_tax_type_and_it_is_cleared_for_the_resubmission(): void
    {
        $verification = $this->submitVerification();
        $this->actingAs($this->admin)->post("/admin/kyc-verifications/{$verification->public_id}/reject", [
            'rejection_reason' => 'Your BIR certificate lists VAT.', 'flags' => ['tax_type'],
        ])->assertSessionHasNoErrors();

        $this->actingAs($this->owner)->get('/shop/dashboard')
            ->assertInertia(fn ($page) => $page
                ->where('kyc.previous.flags', ['tax_type'])
                ->missing('kyc.previous.confirmed.tax_type')
                ->has('kyc.previous.confirmed.tin'));
    }

    public function test_only_admins_can_review_verifications(): void
    {
        $verification = $this->matchingVerification();

        // The role middleware turns non-admins away (it redirects rather than returning 403).
        $response = $this->actingAs($this->owner)
            ->post("/admin/kyc-verifications/{$verification->public_id}/approve");

        $response->assertRedirect();
        $this->assertSame('pending', $verification->refresh()->status);
        $this->assertSame(0, Order::where('user_id', $this->owner->id)->count());
    }
}
