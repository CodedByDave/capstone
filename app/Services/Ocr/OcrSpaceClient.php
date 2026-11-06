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

    /** The free OCR.space service fails now and then (e.g. "E502: Error during OCR"), so a failed try is repeated. */
    private const ATTEMPTS = 3;

    public function extractText(UploadedFile $file): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $contents = $file->get();
        $name = $file->getClientOriginalName();

        for ($attempt = 1; $attempt <= self::ATTEMPTS; $attempt++) {
            // A second engine is tried on the last attempt in case the first one keeps failing.
            $engine = $attempt === self::ATTEMPTS ? 1 : (int) config('services.ocr_space.engine', 2);
            $text = $this->recognise($contents, $name, $engine, $attempt);

            if ($text !== null) {
                return $text;
            }
        }

        return null;
    }

    private function recognise(string $contents, string $name, int $engine, int $attempt): ?string
    {
        try {
            $response = Http::withHeaders(['apikey' => config('services.ocr_space.key')])
                ->timeout((int) config('services.ocr_space.timeout', 30))
                ->attach('file', $contents, $name)
                ->post(config('services.ocr_space.url'), [
                    'language' => 'eng',
                    'OCREngine' => (string) $engine,
                    'scale' => 'true',
                    'detectOrientation' => 'true',
                    'isOverlayRequired' => 'false',
                ]);

            if (! $response->successful()) {
                Log::warning('OCR.space request failed', ['status' => $response->status(), 'attempt' => $attempt]);

                return null;
            }

            $payload = $response->json();

            if (($payload['IsErroredOnProcessing'] ?? false) || empty($payload['ParsedResults'])) {
                Log::warning('OCR.space could not process document', [
                    'error' => $payload['ErrorMessage'] ?? null,
                    'attempt' => $attempt,
                ]);

                return null;
            }

            $text = collect($payload['ParsedResults'])
                ->pluck('ParsedText')
                ->filter()
                ->implode("
");

            return $text === '' ? null : $text;
        } catch (Throwable $e) {
            Log::warning('OCR.space request threw', ['error' => $e->getMessage(), 'attempt' => $attempt]);

            return null;
        }
    }
}
