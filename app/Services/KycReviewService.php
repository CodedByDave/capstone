<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

/**
 * Builds the side-by-side KYC review the admin sees: what OCR read from the
 * documents against what the shop typed and confirmed.
 */
class KycReviewService
{
    /** Fields and documents an admin can flag as wrong when rejecting. */
    public const FLAGGABLE = [
        'tin', 'tax_type', 'registered_name', 'registered_address',
        'bir_expiry_date', 'dti_expiry_date', 'mayors_expiry_date', 'sanitary_expiry_date',
        'kyc_bir', 'kyc_dti', 'kyc_mayors', 'kyc_sanitary',
    ];

    private const DOCS = [
        'bir' => 'BIR',
        'dti' => 'DTI',
        'mayors' => "Mayor's permit",
        'sanitary' => 'Sanitary permit',
    ];

    /** @return array<int, array{key: string, label: string}> */
    public function flagOptions(): array
    {
        $options = [
            ['key' => 'tin', 'label' => 'TIN'],
            ['key' => 'tax_type', 'label' => 'Tax type (VAT / Non-VAT)'],
            ['key' => 'registered_name', 'label' => 'Registered name'],
            ['key' => 'registered_address', 'label' => 'Registered address'],
        ];
        foreach (self::DOCS as $doc => $label) {
            $options[] = ['key' => "{$doc}_expiry_date", 'label' => "{$label} expiry date"];
        }
        foreach (self::DOCS as $doc => $label) {
            $options[] = ['key' => "kyc_{$doc}", 'label' => "{$label} document"];
        }

        return $options;
    }

    /**
     * @param  Model  $order  an Order or KycVerification
     * @return array<int, array{key: string, label: string, ocr: ?string, typed: ?string, confirmed: ?string, status: string}>
     */
    public function rows(Model $order): array
    {
        $ocr = $order->kyc_ocr_data ?? [];
        $confirmed = $order->kyc_confirmed_data ?? [];

        if ($ocr === [] && $confirmed === []) {
            return [];
        }

        $first = fn (string $field) => collect(['bir', 'dti', 'mayors'])
            ->map(fn ($d) => $ocr[$d][$field] ?? null)
            ->filter()
            ->first();

        $address = collect([$order->block_street, $order->barangay, $order->municipality, $order->postal_code])
            ->filter()
            ->implode(', ');

        $confirmedTin = preg_replace('/\D/', '', (string) ($confirmed['tin'] ?? ''));

        $rows = [
            $this->row('tin', 'TIN', $first('tin'), null, $confirmed['tin'] ?? null,
                fn ($o) => preg_replace('/\D/', '', $o) === $confirmedTin),
            ...$this->taxTypeRow($first('tax_type'), $confirmed['tax_type'] ?? null),
            $this->row('registered_name', 'Registered name', $first('registered_name'), $order->shop_name, $confirmed['registered_name'] ?? null,
                fn ($o) => $this->namesMatch($o, $order->shop_name)),
            $this->row('registered_address', 'Registered address', $first('registered_address'), $address, $confirmed['registered_address'] ?? null,
                fn ($o) => $this->addressMatches($o, [$order->block_street, $order->barangay, $order->municipality])),
        ];

        foreach (self::DOCS as $doc => $label) {
            $entered = $order->{"{$doc}_expiry_date"}?->toDateString();
            if ($entered === null && empty($ocr[$doc])) {
                continue;
            }
            $rows[] = $this->row("{$doc}_expiry_date", "{$label} expiry date", $ocr[$doc]['expiry_date'] ?? null, null, $entered,
                fn ($o) => $o === $entered);
        }

        return $rows;
    }

    private const TAX_TYPES = ['vat' => 'VAT', 'non_vat' => 'Non-VAT'];

    /** Shown only when the owner confirmed a tax type (older submissions did not). */
    private function taxTypeRow(?string $ocr, ?string $confirmed): array
    {
        if ($confirmed === null) {
            return [];
        }

        $row = $this->row(
            'tax_type', 'Tax type',
            $ocr === null ? null : (self::TAX_TYPES[$ocr] ?? $ocr),
            null,
            self::TAX_TYPES[$confirmed] ?? $confirmed,
            fn ($o) => $o === (self::TAX_TYPES[$confirmed] ?? $confirmed),
        );

        return [$row];
    }

    private function row(string $key, string $label, ?string $ocr, ?string $typed, ?string $confirmed, callable $matches): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'ocr' => $ocr,
            'typed' => $typed,
            'confirmed' => $confirmed,
            'status' => $ocr === null || $ocr === '' ? 'unread' : ($matches($ocr) ? 'match' : 'mismatch'),
        ];
    }

    private function normalize(?string $value): string
    {
        $lower = mb_strtolower((string) $value);

        return trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9]+/', ' ', $lower)));
    }

    private function namesMatch(string $ocr, ?string $typed): bool
    {
        $a = $this->normalize($ocr);
        $b = $this->normalize($typed);
        if ($a === '' || $b === '') {
            return false;
        }
        similar_text($a, $b, $percent);

        return str_contains($a, $b) || str_contains($b, $a) || $percent >= 80;
    }

    /** @param array<int, ?string> $parts */
    private function addressMatches(string $ocr, array $parts): bool
    {
        $haystack = $this->normalize($ocr);
        $tokens = collect($parts)
            ->flatMap(fn ($p) => explode(' ', $this->normalize($p)))
            ->filter(fn ($t) => strlen($t) > 2 && ! in_array($t, ['city', 'the'], true))
            ->unique()
            ->values();

        if ($tokens->isEmpty()) {
            return false;
        }

        return $tokens->filter(fn ($t) => str_contains($haystack, $t))->count() / $tokens->count() >= 0.6;
    }
}
