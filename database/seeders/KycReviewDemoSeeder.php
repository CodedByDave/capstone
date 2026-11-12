<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Models\KycVerification;
use App\Models\Shop;
use App\Models\User;
use App\Services\KycDocumentVault;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * A pending KYC application for testing the admin review: every detail matches what OCR read from
 * the documents except one, the Mayor's permit expiry date, so Approve stays locked.
 *
 *   php artisan db:seed --class=KycReviewDemoSeeder
 *
 * Re-running replaces the demo application with a fresh pending one.
 */
class KycReviewDemoSeeder extends Seeder
{
    public const OWNER_EMAIL = 'kyc.review.demo@laundryhub.test';

    private const SHOP_NAME = 'Bula Fresh Laundry';

    private const TIN = '456-789-123-000';

    private const ADDRESS = '27 Mabini St, Malagasang II-A, Imus, Cavite';

    public function run(KycDocumentVault $vault): void
    {
        $owner = User::updateOrCreate(
            ['email' => self::OWNER_EMAIL],
            [
                'name' => 'Rosa Dimaculangan',
                'password' => Hash::make('password'),
                'role' => AccountType::ShopOwner->value,
                'email_verified_at' => now(),
            ],
        );

        Shop::updateOrCreate(
            ['owner_id' => $owner->id],
            [
                'shop_name' => self::SHOP_NAME,
                'phone' => '09171239876',
                'block_street' => '27 Mabini St',
                'barangay' => 'Malagasang II-A',
                'municipality' => 'Imus',
                'postal_code' => '4103',
                'status' => 'pending_setup',
            ],
        );

        // Start over each run, removing the stored documents of the old demo application.
        KycVerification::where('user_id', $owner->id)->get()->each(function (KycVerification $old) use ($vault) {
            foreach (['kyc_bir', 'kyc_dti', 'kyc_mayors', 'kyc_sanitary'] as $column) {
                if ($vault->exists($old->{$column})) {
                    $vault->delete($old->{$column});
                }
            }
            $old->delete();
        });

        $birExpiry = Carbon::today()->addYears(2)->endOfYear()->toDateString();
        $dtiExpiry = Carbon::today()->addYears(3)->startOfMonth()->toDateString();
        $mayorsOnPermit = Carbon::today()->addYear()->startOfYear()->addMonths(2)->endOfMonth()->toDateString();
        // The one mismatch: the owner typed a later date than the permit shows.
        $mayorsTyped = Carbon::parse($mayorsOnPermit)->addMonths(3)->endOfMonth()->toDateString();

        $read = fn (string $expiry) => [
            'tin' => self::TIN,
            'registered_name' => mb_strtoupper(self::SHOP_NAME),
            'registered_address' => self::ADDRESS,
            'tax_type' => 'non_vat',
            'expiry_date' => $expiry,
        ];

        $verification = KycVerification::create([
            'user_id' => $owner->id,
            'status' => KycVerification::STATUS_PENDING,
            'shop_name' => self::SHOP_NAME,
            'phone' => '09171239876',
            'block_street' => '27 Mabini St',
            'barangay' => 'Malagasang II-A',
            'municipality' => 'Imus',
            'postal_code' => '4103',
            'bir_expiry_date' => $birExpiry,
            'dti_expiry_date' => $dtiExpiry,
            'mayors_expiry_date' => $mayorsTyped,
            'kyc_ocr_data' => [
                'bir' => $read($birExpiry),
                'dti' => $read($dtiExpiry),
                'mayors' => $read($mayorsOnPermit),
            ],
            'kyc_confirmed_data' => [
                'tin' => self::TIN,
                'tax_type' => 'non_vat',
                'registered_name' => mb_strtoupper(self::SHOP_NAME),
                'registered_address' => self::ADDRESS,
            ],
        ]);

        $folder = "kyc-verifications/{$verification->id}";
        $verification->update([
            'kyc_bir' => $vault->storeContents($this->document('BIR CERTIFICATE OF REGISTRATION (FORM 2303)', [
                'TIN: '.self::TIN,
                'Registered Name: '.mb_strtoupper(self::SHOP_NAME),
                'Registered Address: '.self::ADDRESS,
                'Tax Type: Percentage Tax (Non-VAT)',
                'Valid until: '.$birExpiry,
            ]), $folder, 'png'),
            'kyc_dti' => $vault->storeContents($this->document('DTI BUSINESS NAME REGISTRATION', [
                'Business Name: '.mb_strtoupper(self::SHOP_NAME),
                'Owner TIN: '.self::TIN,
                'Address: '.self::ADDRESS,
                'Valid until: '.$dtiExpiry,
            ]), $folder, 'png'),
            'kyc_mayors' => $vault->storeContents($this->document("MAYOR'S PERMIT - CITY OF IMUS", [
                'Business: '.mb_strtoupper(self::SHOP_NAME),
                'TIN: '.self::TIN,
                'Location: '.self::ADDRESS,
                'Valid until: '.$mayorsOnPermit,
            ]), $folder, 'png'),
        ]);

        $this->command?->info('KYC review demo ready: '.self::SHOP_NAME.' (pending).');
        $this->command?->line("  Admin page: /admin/kyc-verifications/{$verification->public_id}");
        $this->command?->line("  Mismatch:   Mayor's permit expiry date (document {$mayorsOnPermit}, owner typed {$mayorsTyped})");
    }

    /** A plain picture of a permit, so the admin has a document to open beside the review. */
    private function document(string $title, array $lines): string
    {
        $image = imagecreatetruecolor(900, 560);
        $paper = imagecolorallocate($image, 250, 250, 245);
        $ink = imagecolorallocate($image, 30, 41, 59);
        $rule = imagecolorallocate($image, 148, 163, 184);

        imagefilledrectangle($image, 0, 0, 899, 559, $paper);
        imagerectangle($image, 12, 12, 887, 547, $rule);
        imagestring($image, 5, 40, 40, $title, $ink);
        imageline($image, 40, 70, 860, 70, $rule);
        imagestring($image, 3, 40, 84, 'SAMPLE DOCUMENT FOR TESTING - NOT A REAL PERMIT', $rule);

        foreach (array_values($lines) as $i => $line) {
            imagestring($image, 5, 40, 130 + $i * 40, $line, $ink);
        }

        ob_start();
        imagepng($image);
        imagedestroy($image);

        return (string) ob_get_clean();
    }
}
