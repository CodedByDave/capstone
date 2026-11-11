<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Models\BusinessAgreement;
use App\Models\KycVerification;
use App\Models\Shop;
use App\Models\User;
use App\Services\KycDocumentVault;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * KYC documents are encrypted at rest, yet the Super Admin can still open them to review and approve.
 */
class KycDocumentEncryptionTest extends TestCase
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
        Notification::fake();

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

    private function pngBytes(string $salt): string
    {
        return base64_decode(self::PNG).$salt;
    }

    private function submit(array $overrides = []): KycVerification
    {
        $year = now()->addYear()->toDateString();

        $this->actingAs($this->owner)->post('/kyc-verification', $overrides + [
            'kyc_bir' => UploadedFile::fake()->createWithContent('bir.png', $this->pngBytes('bir')),
            'kyc_dti' => UploadedFile::fake()->createWithContent('dti.png', $this->pngBytes('dti')),
            'kyc_mayors' => UploadedFile::fake()->createWithContent('mayors.png', $this->pngBytes('mayors')),
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
        ])->assertSessionHasNoErrors();

        return KycVerification::where('user_id', $this->owner->id)->latest('id')->firstOrFail();
    }

    public function test_uploaded_documents_are_encrypted_on_disk(): void
    {
        $verification = $this->submit();

        foreach (['kyc_bir' => 'bir', 'kyc_dti' => 'dti', 'kyc_mayors' => 'mayors'] as $column => $salt) {
            $path = $verification->{$column};
            $this->assertStringEndsWith('.png.enc', $path);

            $stored = Storage::disk('private')->get($path);
            $this->assertStringNotContainsString("\x89PNG", $stored, 'The file on disk must not be the plain image.');
            $this->assertSame($this->pngBytes($salt), Crypt::decryptString($stored));
        }
    }

    public function test_super_admin_can_open_a_decrypted_document(): void
    {
        $path = $this->submit()->kyc_bir;

        $this->actingAs($this->admin)
            ->get('/admin/kyc-file?path='.urlencode($path))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertSee($this->pngBytes('bir'), false);
    }

    public function test_only_the_super_admin_can_open_documents(): void
    {
        $path = $this->submit()->kyc_bir;

        $response = $this->actingAs($this->owner)->get('/admin/kyc-file?path='.urlencode($path));

        $this->assertNotSame(200, $response->getStatusCode());
        $this->assertStringNotContainsString($this->pngBytes('bir'), (string) $response->getContent());
    }

    public function test_viewer_cannot_read_private_files_outside_the_kyc_folders(): void
    {
        Storage::disk('private')->put('agreements/secret.pdf', 'secret');
        Storage::disk('private')->put('kyc/1/doc.png', 'plain');

        foreach (['agreements/secret.pdf', 'kyc/../agreements/secret.pdf', 'kyc/1/missing.png.enc', '', '/kyc/1/doc.png'] as $path) {
            $this->actingAs($this->admin)
                ->get('/admin/kyc-file?path='.urlencode($path))
                ->assertNotFound();
        }
    }

    public function test_a_document_kept_from_a_rejected_application_stays_encrypted_and_readable(): void
    {
        $first = $this->submit();
        $this->actingAs($this->admin)->post("/admin/kyc-verifications/{$first->public_id}/reject", [
            'rejection_reason' => 'The DTI document is unreadable.',
            'flags' => ['kyc_dti'],
        ])->assertSessionHasNoErrors();

        $second = $this->submit([
            'kyc_bir' => null,
            'kyc_mayors' => null,
            'reuse_kyc' => ['bir', 'mayors'],
        ]);

        $this->assertNotSame($first->kyc_bir, $second->kyc_bir);
        $this->assertStringEndsWith('.enc', $second->kyc_bir);
        $this->assertSame($this->pngBytes('bir'), app(KycDocumentVault::class)->contents($second->kyc_bir));
    }

    public function test_documents_saved_before_encryption_still_open_and_are_encrypted_by_the_command(): void
    {
        $verification = $this->submit();
        $legacyPath = 'kyc-verifications/'.$verification->id.'/legacy.png';
        Storage::disk('private')->put($legacyPath, $this->pngBytes('legacy'));
        $verification->forceFill(['kyc_bir' => $legacyPath])->save();

        $this->actingAs($this->admin)
            ->get('/admin/kyc-file?path='.urlencode($legacyPath))
            ->assertOk()
            ->assertSee($this->pngBytes('legacy'), false);

        $this->artisan('kyc:encrypt-existing')->assertSuccessful();

        $newPath = $verification->refresh()->kyc_bir;
        $this->assertSame($legacyPath.'.enc', $newPath);
        Storage::disk('private')->assertMissing($legacyPath);
        $this->assertSame($this->pngBytes('legacy'), Crypt::decryptString(Storage::disk('private')->get($newPath)));

        // Running it again changes nothing.
        $this->artisan('kyc:encrypt-existing')->assertSuccessful();
        $this->assertSame($newPath, $verification->refresh()->kyc_bir);
    }
}
