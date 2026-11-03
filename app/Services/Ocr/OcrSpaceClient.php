<?php

namespace App\Services\Ocr;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Thin HTTP client for the OCR.space "parse/image" endpoint.
 * Returns the recognised text, or null when OCR is unavailable or fails.
 */
class OcrSpaceClient
{
    public function isConfigured(): bool
    {
        return filled(config('services.ocr_space.key'));
    }

    public function extractText(UploadedFile $file): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withHeaders(['apikey' => config('services.ocr_space.key')])
                ->timeout((int) config('services.ocr_space.timeout', 30))
                ->attach('file', $file->get(), $file->getClientOriginalName())
                ->post(config('services.ocr_space.url'), [
                    'language' => 'eng',
                    'OCREngine' => (string) config('services.ocr_space.engine', 2),
                    'scale' => 'true',
                    'detectOrientation' => 'true',
                    'isOverlayRequired' => 'false',
                ]);

            if (! $response->successful()) {
                Log::warning('OCR.space request failed', ['status' => $response->status()]);

                return null;
            }

            $payload = $response->json();

            if (($payload['IsErroredOnProcessing'] ?? false) || empty($payload['ParsedResults'])) {
                Log::warning('OCR.space could not process document', [
                    'error' => $payload['ErrorMessage'] ?? null,
                ]);

                return null;
            }

            return collect($payload['ParsedResults'])
                ->pluck('ParsedText')
                ->filter()
                ->implode("\n");
        } catch (Throwable $e) {
            Log::warning('OCR.space request threw', ['error' => $e->getMessage()]);

            return null;
        }
    }
}
