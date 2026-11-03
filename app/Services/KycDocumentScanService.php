<?php

namespace App\Services;

use App\Services\Ocr\ExpiryDateParser;
use App\Services\Ocr\KycFieldExtractor;
use App\Services\Ocr\OcrSpaceClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;

/**
 * Reads an uploaded KYC document with OCR. The file itself is never stored here.
 *
 * Results are remembered briefly per user and file hash, so that when the shop
 * submits the application the server can recall what OCR really read, instead
 * of trusting values sent back by the browser.
 */
class KycDocumentScanService
{
    private const CACHE_MINUTES = 120;

    public function __construct(
        private OcrSpaceClient $ocr,
        private ExpiryDateParser $dates,
        private KycFieldExtractor $fields,
    ) {}

    /**
     * @return array{available: bool, tin: ?string, registered_name: ?string, registered_address: ?string, tax_type: ?string, expiry_date: ?string, expired: bool}
     */
    public function scan(int $userId, UploadedFile $file): array
    {
        $empty = ['tin' => null, 'registered_name' => null, 'registered_address' => null, 'tax_type' => null, 'expiry_date' => null];

        if (! $this->ocr->isConfigured()) {
            return ['available' => false, 'expired' => false] + $empty;
        }

        $text = $this->ocr->extractText($file);
        $result = $text ? [...$this->fields->extract($text), 'expiry_date' => $this->dates->parse($text)] : $empty;

        if ($text) {
            Cache::put($this->cacheKey($userId, $file), $result, now()->addMinutes(self::CACHE_MINUTES));
        }

        return [
            'available' => true,
            'expired' => $result['expiry_date'] !== null && $result['expiry_date'] <= now()->toDateString(),
        ] + $result;
    }

    /** What OCR read from this exact file earlier, or null if it was never scanned by this user. */
    public function recall(int $userId, UploadedFile $file): ?array
    {
        return Cache::get($this->cacheKey($userId, $file));
    }

    private function cacheKey(int $userId, UploadedFile $file): string
    {
        return "kyc_scan.{$userId}.".sha1_file($file->getRealPath());
    }
}
