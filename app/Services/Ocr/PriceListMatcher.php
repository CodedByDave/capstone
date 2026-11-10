<?php

namespace App\Services\Ocr;

use App\Models\SupplierPriceList;

/**
 * Compares the text OCR read from a price-list photo with the items a supplier typed in.
 *
 * For every item it looks for the line that best matches the item's name, then checks whether
 * the typed price appears on that line (or the line after it, since OCR often splits a table
 * row in two). The result only flags differences; it never blocks the supplier.
 */
class PriceListMatcher
{
    public const RESULT_MATCHED = 'matched';

    public const RESULT_PRICE_MISMATCH = 'price_mismatch';

    public const RESULT_NOT_FOUND = 'not_found';

    /** How close a line's words must be to an item name (0–1) to count as the same product. */
    private const NAME_THRESHOLD = 0.8;

    /** How many lines below a product name its price may sit when OCR splits the row. */
    private const LOOKAHEAD_LINES = 3;

    /** Words a price-list unit column holds; a line of only these still belongs to the row above. */
    private const UNIT_WORDS = [
        'php', 'peso', 'pesos', 'each', 'per', 'unit', 'units',
        'kg', 'kgs', 'kilo', 'kilos', 'g', 'gram', 'grams', 'ml', 'liter', 'liters', 'litre', 'litres',
        'gal', 'gallon', 'gallons', 'oz', 'lb', 'lbs', 'pc', 'pcs', 'piece', 'pieces',
        'pack', 'packs', 'sachet', 'sachets', 'bottle', 'bottles', 'box', 'boxes', 'sack', 'sacks',
        'bag', 'bags', 'can', 'cans', 'case', 'cases', 'roll', 'rolls', 'set', 'sets', 'dozen', 'ream',
    ];

    /**
     * @param  iterable<array{public_id: string, name: string, unit_price: float|string}>  $items
     * @return array{status: string, results: list<array<string, mixed>>}
     */
    public function match(?string $ocrText, iterable $items): array
    {
        $lines = $this->lines($ocrText ?? '');

        if ($lines === []) {
            return ['status' => SupplierPriceList::STATUS_UNREADABLE, 'results' => []];
        }

        $results = [];

        foreach ($items as $item) {
            $results[] = $this->matchItem($item, $lines);
        }

        $allMatched = $results !== []
            && collect($results)->every(fn (array $result) => $result['result'] === self::RESULT_MATCHED);

        return [
            'status' => $allMatched ? SupplierPriceList::STATUS_VERIFIED : SupplierPriceList::STATUS_MISMATCH,
            'results' => $results,
        ];
    }

    /**
     * @param  array{public_id: string, name: string, unit_price: float|string}  $item
     * @param  list<array{words: list<string>, prices: list<float>}>  $lines
     */
    private function matchItem(array $item, array $lines): array
    {
        $expected = round((float) $item['unit_price'], 2);
        $nameWords = $this->words($item['name']);
        $base = [
            'item' => $item['public_id'],
            'name' => $item['name'],
            'expected_price' => $expected,
            'found_price' => null,
        ];

        $bestIndex = null;
        $bestScore = 0.0;

        foreach ($lines as $index => $line) {
            $score = $this->nameScore($nameWords, $line['words']);

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestIndex = $index;
            }
        }

        if ($bestIndex === null || $bestScore < self::NAME_THRESHOLD) {
            return [...$base, 'result' => self::RESULT_NOT_FOUND];
        }

        $candidates = $lines[$bestIndex]['prices'] !== []
            ? $lines[$bestIndex]['prices']
            : $this->pricesBelow($lines, $bestIndex);

        foreach ($candidates as $price) {
            if (abs($price - $expected) < 0.005) {
                return [...$base, 'found_price' => $price, 'result' => self::RESULT_MATCHED];
            }
        }

        // Prices usually sit in the right-most column, so the last number on the line is reported.
        $found = $lines[$bestIndex]['prices'] !== []
            ? end($lines[$bestIndex]['prices'])
            : ($candidates !== [] ? end($candidates) : null);

        return [...$base, 'found_price' => $found === false ? null : $found, 'result' => self::RESULT_PRICE_MISMATCH];
    }

    /**
     * OCR often splits a table row into one line per cell ("Liquid Detergent", "gallon", "450.00").
     * Lines holding only a unit word are skipped and the first line with a number is the price.
     * Any other line with words is another product, so the search stops there and an item never
     * borrows the price of the item listed below it.
     *
     * @param  list<array{words: list<string>, prices: list<float>}>  $lines
     * @return list<float>
     */
    private function pricesBelow(array $lines, int $index): array
    {
        for ($i = $index + 1; $i <= $index + self::LOOKAHEAD_LINES && isset($lines[$i]); $i++) {
            $line = $lines[$i];
            $onlyUnits = array_diff($line['words'], self::UNIT_WORDS) === [];

            if ($line['prices'] !== [] && $onlyUnits) {
                return $line['prices'];
            }

            if (! $onlyUnits) {
                return [];
            }
        }

        return [];
    }

    /** Share of the item's words found on the line, allowing one wrong letter in longer words. */
    private function nameScore(array $nameWords, array $lineWords): float
    {
        if ($nameWords === [] || $lineWords === []) {
            return 0.0;
        }

        $found = 0;

        foreach ($nameWords as $word) {
            foreach ($lineWords as $candidate) {
                if ($word === $candidate
                    || rtrim($word, 's') === rtrim($candidate, 's')
                    || (mb_strlen($word) >= 5 && levenshtein($word, $candidate) <= 1)) {
                    $found++;

                    break;
                }
            }
        }

        return $found / count($nameWords);
    }

    /** @return list<array{words: list<string>, prices: list<float>}> */
    private function lines(string $text): array
    {
        $lines = [];

        foreach (preg_split('/\R/u', $text) ?: [] as $raw) {
            $raw = trim($raw);

            if ($raw === '') {
                continue;
            }

            $lines[] = ['words' => $this->words($raw), 'prices' => $this->prices($raw)];
        }

        return $lines;
    }

    /** @return list<string> Lower-case words of two or more letters, numbers left out. */
    private function words(string $text): array
    {
        $text = mb_strtolower($text);
        preg_match_all('/[\p{L}][\p{L}\'-]+/u', $text, $matches);

        return array_values(array_map(fn (string $word) => str_replace(["'", '-'], '', $word), $matches[0]));
    }

    /**
     * @return list<float> Every number on the line read as money ("1,250.00", "₱85", "85.5"), leaving
     *                     out sizes and counts such as "1 gal", "500ml" or "12 pcs".
     */
    private function prices(string $text): array
    {
        preg_match_all(
            '/(?:\d{1,3}(?:,\d{3})+|\d+)(?:\.\d{1,2})?(?!\d|[.,]\d|\s*(?:kgs?|g|grams?|ml|l|liters?|litres?|gal|gallons?|oz|lbs?|pcs?|pieces?|packs?|sachets?|bottles?|boxes|box|sacks?|x)\b)/iu',
            $text,
            $matches,
        );

        return array_values(array_map(
            fn (string $number) => round((float) str_replace(',', '', $number), 2),
            $matches[0],
        ));
    }
}
