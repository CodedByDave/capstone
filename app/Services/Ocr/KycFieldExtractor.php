<?php

namespace App\Services\Ocr;

/**
 * Pulls the TIN, registered name and registered address out of OCR text of a
 * BIR 2303 / DTI certificate. Best effort: layouts vary, so every value is
 * optional and the shop can correct it in the review form.
 */
class KycFieldExtractor
{
    private const NAME_LABELS = '/^\s*(?:registered\s+(?:name|title)|taxpayer(?:\'?s)?\s+name|name\s+of\s+(?:business|taxpayer)|business\s+name|registrant(?:\'?s)?\s+name)\s*(?::|-\s|$)\s*(.*)$/i';

    private const ADDRESS_LABELS = '/^\s*(?:registered\s+address|business\s+address|address\s+of\s+(?:business|taxpayer)|principal\s+(?:place|office)[a-z\s]*|address)\s*(?::|-\s|$)\s*(.*)$/i';

    /** @return array{tin: string|null, registered_name: string|null, registered_address: string|null, tax_type: string|null} */
    public function extract(string $text): array
    {
        $lines = array_values(array_filter(
            array_map(fn ($l) => trim(preg_replace('/\s+/', ' ', $l)), preg_split('/\R/', $text)),
            fn ($l) => $l !== '',
        ));

        return [
            'tin' => $this->tin($text),
            'registered_name' => $this->valueAfter($lines, self::NAME_LABELS, 1),
            'registered_address' => $this->valueAfter($lines, self::ADDRESS_LABELS, 2),
            'tax_type' => $this->taxType($text),
        ];
    }

    /**
     * 'vat' when the certificate lists Value Added Tax, 'non_vat' when it lists Percentage Tax.
     * If it mentions both or neither, the answer is unknown and the owner chooses.
     */
    private function taxType(string $text): ?string
    {
        $vat = (bool) preg_match('/value[\s-]*added[\s-]*tax|\bvat\b/i', preg_replace('/non[\s-]*vat/i', '', $text));
        $nonVat = (bool) preg_match('/percentage[\s-]*tax|non[\s-]*vat/i', $text);

        return match (true) {
            $vat && ! $nonVat => 'vat',
            $nonVat && ! $vat => 'non_vat',
            default => null,
        };
    }

    private function tin(string $text): ?string
    {
        $pattern = '/(\d{3})[\s-]?(\d{3})[\s-]?(\d{3})(?:[\s-]?(\d{3,5}))?/';

        // Prefer a number that follows a "TIN" label.
        if (preg_match('/T\.?I\.?N\.?[^0-9]{0,25}'.substr($pattern, 1, -1).'/i', $text, $m)) {
            return $this->formatTin($m);
        }

        // Otherwise accept only the dashed form, to avoid matching random digits.
        if (preg_match('/\b(\d{3})-(\d{3})-(\d{3})(?:-(\d{3,5}))?\b/', $text, $m)) {
            return $this->formatTin($m);
        }

        return null;
    }

    private function formatTin(array $m): string
    {
        return implode('-', array_filter([$m[1], $m[2], $m[3], $m[4] ?? null]));
    }

    /** @param  array<int, string>  $lines */
    private function valueAfter(array $lines, string $labelPattern, int $maxLines): ?string
    {
        foreach ($lines as $i => $line) {
            if (! preg_match($labelPattern, $line, $m)) {
                continue;
            }

            $parts = [];
            if (trim($m[1]) !== '') {
                $parts[] = trim($m[1]);
            }

            // Continue onto following lines until another "Label:" line begins.
            for ($j = $i + 1; $j < count($lines) && count($parts) < $maxLines; $j++) {
                if (preg_match('/^[A-Za-z][A-Za-z\s\/\.\']{1,40}:/', $lines[$j])) {
                    break;
                }
                $parts[] = $lines[$j];
                if ($maxLines === 1) {
                    break;
                }
            }

            $value = trim(implode(', ', $parts), ' ,:-');
            if ($value !== '') {
                return mb_substr($value, 0, 255);
            }
        }

        return null;
    }
}
