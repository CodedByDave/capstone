<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KycDocumentScanTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_scan(): void
    {
        $this->postJson('/kyc/scan-expiry', [])->assertUnauthorized();
    }

    public function test_returns_expiry_date_found_by_ocr(): void
    {
        config(['services.ocr_space.key' => 'test-key']);
        Http::fake([
            'api.ocr.space/*' => Http::response([
                'IsErroredOnProcessing' => false,
                'ParsedResults' => [['ParsedText' => 'Valid until December 31, 2099']],
            ]),
        ]);

        $this->actingAs(User::factory()->create())
            ->postJson('/kyc/scan-expiry', ['document' => UploadedFile::fake()->image('permit.jpg')])
            ->assertOk()
            ->assertJson(['available' => true, 'expiry_date' => '2099-12-31', 'expired' => false]);
    }

    public function test_reports_unavailable_when_no_api_key(): void
    {
        config(['services.ocr_space.key' => null]);

        $this->actingAs(User::factory()->create())
            ->postJson('/kyc/scan-expiry', ['document' => UploadedFile::fake()->image('permit.jpg')])
            ->assertOk()
            ->assertJson(['available' => false, 'expiry_date' => null]);
    }

    public function test_ocr_failure_returns_no_date_instead_of_error(): void
    {
        config(['services.ocr_space.key' => 'test-key']);
        Http::fake(['api.ocr.space/*' => Http::response('boom', 500)]);

        $this->actingAs(User::factory()->create())
            ->postJson('/kyc/scan-expiry', ['document' => UploadedFile::fake()->image('permit.jpg')])
            ->assertOk()
            ->assertJson(['available' => true, 'expiry_date' => null]);
    }

    public function test_rejects_unsupported_file_types(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson('/kyc/scan-expiry', ['document' => UploadedFile::fake()->create('x.exe', 10)])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('document');
    }
}
