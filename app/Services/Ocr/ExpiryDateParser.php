<?php

namespace App\Services\Ocr;

use Carbon\CarbonImmutable;

/**
 * Finds the most likely expiry date in OCR text of a permit/certificate.
 * Pure text logic: no I/O, so it is easy to unit test.
 */
class ExpiryDateParser
{
    private const MONTHS = [
        'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'may' => 5, 'jun' => 6,
        'jul' => 7, 'aug' => 8, 'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12,
    ];

    private const KEYWORDS = '/(valid\s*(?:until|thru|through|to)|validity|expir\w*|until|good\s+until|valid\s+up\s+to|date\s+of\s+expiry)/i';

    /** @return string|null Date as Y-m-d */
    public function parse(string $text): ?string
    {
        $dates = $this->findDates($text);
        if ($dates === []) {
            return null;
        }

        // Dates that appear right after an expiry keyword are the strongest signal.
        $keywordMatches = [];
        if (preg_match_all(self::KEYWORDS, $text, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as [$word, $wordPos]) {
                $end = $wordPos + strlen($word);
                foreach ($dates as $d) {
                    // Allow a window after the keyword (covers "valid from X to Y" ranges).
                    if ($d['pos'] >= $end && $d['pos'] - $end <= 70) {
                        $keywordMatches[] = $d['date'];
                    }
                }
            }
        }

        $pool = $keywordMatches !== [] ? $keywordMatches : array_column($dates, 'date');

        // Latest date wins: expiry is always the last date on the document.
        return collect($pool)->max()?->toDateString();
    }

    /** @return array<int, array{date: CarbonImmutable, pos: int}> */
    private function findDates(string $text): array
    {
        $found = [];
        $months = 'jan(?:uary)?|feb(?:ruary)?|mar(?:ch)?|apr(?:il)?|may|june?|july?|aug(?:ust)?|sept?(?:ember)?|oct(?:ober)?|nov(?:ember)?|dec(?:ember)?';

        // January 31, 2027 / Jan. 31 2027
        if (preg_match_all("/\b($months)\.?\s+(\d{1,2})(?:st|nd|rd|th)?,?\s+(\d{4})\b/i", $text, $m, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            foreach ($m as $x) {
                $this->add($found, (int) $x[3][0], $this->month($x[1][0]), (int) $x[2][0], $x[0][1]);
            }
        }

        // 31 January 2027
        if (preg_match_all("/\b(\d{1,2})(?:st|nd|rd|th)?\s+($months)\.?,?\s+(\d{4})\b/i", $text, $m, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            foreach ($m as $x) {
                $this->add($found, (int) $x[3][0], $this->month($x[2][0]), (int) $x[1][0], $x[0][1]);
            }
        }

        // 2027-01-31 / 2027/01/31
        if (preg_match_all('/\b(\d{4})[-\/.](\d{1,2})[-\/.](\d{1,2})\b/', $text, $m, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            foreach ($m as $x) {
                $this->add($found, (int) $x[1][0], (int) $x[2][0], (int) $x[3][0], $x[0][1]);
            }
        }

        // 01/31/2027 (PH permits are usually MM/DD/YYYY; fall back to DD/MM/YYYY when that is the only valid reading)
        if (preg_match_all('/\b(\d{1,2})[-\/.](\d{1,2})[-\/.](\d{4})\b/', $text, $m, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            foreach ($m as $x) {
                $a = (int) $x[1][0];
                $b = (int) $x[2][0];
                $year = (int) $x[3][0];
                if ($a <= 12) {
                    $this->add($found, $year, $a, $b, $x[0][1]);
                } else {
                    $this->add($found, $year, $b, $a, $x[0][1]);
                }
            }
        }

        usort($found, fn ($l, $r) => $l['pos'] <=> $r['pos']);

        return $found;
    }

    private function month(string $name): int
    {
        return self::MONTHS[strtolower(substr($name, 0, 3))];
    }

    private function add(array &$found, int $year, int $month, int $day, int $pos): void
    {
        if ($year < 2000 || $year > 2100 || ! checkdate($month, $day, $year)) {
            return;
        }

        $found[] = ['date' => CarbonImmutable::create($year, $month, $day), 'pos' => $pos];
    }
}
