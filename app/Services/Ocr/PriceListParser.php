<?php

namespace App\Services\Ocr;

/**
 * Turns the text OCR read from a printed price list into product rows (name, unit, price).
 *
 * A row is a line with a product name and a price ("Liquid Detergent 1 gal ₱450.00"). OCR often
 * splits a table row into one line per cell ("Liquid Detergent", "gallon", "450.00"), so a name
 * without a price takes the unit and price from the next lines when those hold nothing else.
 * Headings, contact details and lines without a name are left out. The supplier reviews every
 * row before anything is saved.
 */
class PriceListParser
{
    /** How many lines below a product name its unit and price may sit. */
    private const LOOKAHEAD_LINES = 3;

    private const UNIT_WORDS = [
        'kg', 'kgs', 'kilo', 'kilos', 'g', 'gram', 'grams', 'ml', 'l', 'liter', 'liters', 'litre', 'litres',
        'gal', 'gallon', 'gallons', 'oz', 'lb', 'lbs', 'pc', 'pcs', 'piece', 'pieces',
        'pack', 'packs', 'sachet', 'sachets', 'bottle', 'bottles', 'box', 'boxes', 'sack', 'sacks',
        'bag', 'bags', 'can', 'cans', 'case', 'cases', 'roll', 'rolls', 'set', 'sets', 'dozen', 'ream',
        'each', 'unit', 'units',
    ];

    /** Words around a unit or price that are not part of the product name. */
    private const FILLER_WORDS = ['php', 'peso', 'pesos', 'per', 'only', 'price', 'srp'];

    /** A heading line, e.g. "PRICE LIST", "Item  Unit  Price". */
    private const HEADING = '/^(?:price\s*list|item|items|product|products|description|particulars|unit|units|price|prices|qty|quantity|amount|total|no|\s)+$/iu';

    /** A money amount ("₱1,250.00", "P120", "85.5"); the amount is group 1. */
    private const MONEY = '/(?:₱|php\s*|\bp(?=\d))?((?:\d{1,3}(?:,\d{3})+|\d+)(?:\.\d{1,2})?)(?![\d.,]*\d)/iu';

    private const CONTACT = '/\b(?:tel|telephone|phone|mobile|cell|call|contact|email|e-mail|address|website|fb|facebook)\b|@|\b0?9\d{2}[\s-]?\d{3}[\s-]?\d{4}\b/iu';

    /** @return list<array{name: string, unit: string|null, unit_price: float}> */
    public function parse(?string $text): array
    {
        $lines = array_values(array_filter(
            array_map('trim', preg_split('/\R/u', (string) $text) ?: []),
            fn (string $line) => $line !== '' && ! preg_match(self::CONTACT, $line),
        ));

        $rows = [];
        $seen = [];

        for ($i = 0, $count = count($lines); $i < $count; $i++) {
            [$name, $unit, $price] = $this->read($lines[$i]);

            if ($name === '' || preg_match(self::HEADING, $name)) {
                continue;
            }

            // A split row: the unit and price sit on the lines below the name.
            for ($next = $i + 1; $price === null && $next <= $i + self::LOOKAHEAD_LINES && $next < $count; $next++) {
                [$nextName, $nextUnit, $nextPrice] = $this->read($lines[$next]);
                if ($nextName !== '') {
                    break;
                }
                $unit ??= $nextUnit;
                $price = $nextPrice;
                if ($price !== null) {
                    $i = $next;
                }
            }

            $key = mb_strtolower($name);
            if ($price === null || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $rows[] = ['name' => $name, 'unit' => $unit, 'unit_price' => $price];
        }

        return $rows;
    }

    /**
     * The product name, unit and last price on one line.
     *
     * @return array{0: string, 1: string|null, 2: float|null}
     */
    private function read(string $line): array
    {
        $units = implode('|', array_map(fn ($u) => preg_quote($u, '/'), self::UNIT_WORDS));
        $unit = null;
        $soldBy = false;
        $text = $line;

        // "per sack" or "/ gal" says how it is sold.
        if (preg_match('/(?:\bper\s+|\/\s*)('.$units.')\b/iu', $text, $match)) {
            $unit = mb_strtolower($match[1]);
            $soldBy = true;
            $text = str_replace($match[0], ' ', $text);
        }

        // Sizes such as "1 gal", "25kg" and "500 ml"; counts such as "x12" or "12s".
        if (preg_match('/\b(\d+(?:\.\d+)?\s*(?:'.$units.'))\b/iu', $text, $match)) {
            $unit ??= mb_strtolower(preg_replace('/\s+/', ' ', $match[1]));
            $text = str_replace($match[0], ' ', $text);
        }
        $text = preg_replace('/\bx\s*\d+\b|\b\d+\s*x\b/iu', ' ', $text);

        $price = null;
        if (preg_match_all(self::MONEY, $text, $matches) && $matches[1] !== []) {
            $price = round((float) str_replace(',', '', end($matches[1])), 2);
            $text = preg_replace(self::MONEY, ' ', $text);
        }

        $text = preg_replace('/[₱|:=\/\\\\*_•·]+|\.{2,}|-{2,}/u', ' ', $text);
        $words = array_values(array_filter(
            preg_split('/\s+/u', trim($text)) ?: [],
            function (string $word) {
                $plain = mb_strtolower(trim($word, ".,;()[]-'\""));

                return $plain !== '' && preg_match('/\p{L}/u', $plain) && ! in_array($plain, self::FILLER_WORDS, true);
            },
        ));

        // "Bleach gallon 450" or "Powder Soap 25kg sack": a unit word at the end belongs to the unit.
        $last = mb_strtolower(trim((string) end($words), '.,'));
        if (! $soldBy && $words !== [] && in_array($last, self::UNIT_WORDS, true) && ($unit === null || count($words) > 1)) {
            $unit = $unit === null ? $last : "{$unit} {$last}";
            array_pop($words);
        }

        $name = trim(implode(' ', $words), ' .,;-');

        return [mb_strlen($name) >= 2 ? mb_substr($name, 0, 255) : '', $unit, $price];
    }
}
