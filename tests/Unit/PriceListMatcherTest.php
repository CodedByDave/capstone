<?php

use App\Models\SupplierPriceList;
use App\Services\Ocr\PriceListMatcher;

function matcherItems(array $prices): array
{
    return collect($prices)->map(fn ($price, $name) => [
        'public_id' => 'id-'.$name,
        'name' => $name,
        'unit_price' => $price,
    ])->values()->all();
}

test('every item found with its price is verified', function () {
    $outcome = (new PriceListMatcher)->match(
        "SUPPLY PRICE LIST\nLiquid Detergent (1 gal) .... P 1,250.00\nFabric Softener ₱380",
        matcherItems(['Liquid Detergent' => '1250.00', 'Fabric Softener' => 380]),
    );

    expect($outcome['status'])->toBe(SupplierPriceList::STATUS_VERIFIED)
        ->and(collect($outcome['results'])->pluck('result')->unique()->all())->toBe(['matched']);
});

test('a price split onto the next line still matches', function () {
    $outcome = (new PriceListMatcher)->match("Bleach\n95.50", matcherItems(['Bleach' => 95.5]));

    expect($outcome['status'])->toBe(SupplierPriceList::STATUS_VERIFIED);
});

test('one misread letter in a longer word is tolerated', function () {
    $outcome = (new PriceListMatcher)->match('Detergemt 450', matcherItems(['Detergent' => 450]));

    expect($outcome['results'][0]['result'])->toBe(PriceListMatcher::RESULT_MATCHED);
});

test('a different price is reported with the price on the photo', function () {
    $outcome = (new PriceListMatcher)->match('Fabric Softener 1 L 400.00', matcherItems(['Fabric Softener' => 380]));

    expect($outcome['status'])->toBe(SupplierPriceList::STATUS_MISMATCH)
        ->and($outcome['results'][0])
        ->result->toBe(PriceListMatcher::RESULT_PRICE_MISMATCH)
        ->found_price->toEqual(400);
});

test('an item missing from the photo is reported as not found', function () {
    $outcome = (new PriceListMatcher)->match('Bleach 95', matcherItems(['Bleach' => 95, 'Stain Remover' => 150]));

    expect($outcome['status'])->toBe(SupplierPriceList::STATUS_MISMATCH)
        ->and($outcome['results'][1]['result'])->toBe(PriceListMatcher::RESULT_NOT_FOUND);
});

test('no readable text is unreadable', function (?string $text) {
    expect((new PriceListMatcher)->match($text, matcherItems(['Bleach' => 95]))['status'])
        ->toBe(SupplierPriceList::STATUS_UNREADABLE);
})->with([null, '', "  \n "]);

test('a size or count on the line is not mistaken for the price', function () {
    $outcome = (new PriceListMatcher)->match('Detergent 1 gal 450.00', matcherItems(['Detergent' => 1]));

    expect($outcome['results'][0])
        ->result->toBe(PriceListMatcher::RESULT_PRICE_MISMATCH)
        ->found_price->toEqual(450);
});

test('an item never borrows the price of the item on the next line', function () {
    $outcome = (new PriceListMatcher)->match("Bleach\nSoap 95", matcherItems(['Bleach' => 95]));

    expect($outcome['results'][0]['result'])->not->toBe(PriceListMatcher::RESULT_MATCHED);
});

test('a plural on the photo still matches a singular item name', function () {
    $outcome = (new PriceListMatcher)->match('Hangers 7.00', matcherItems(['Hanger' => 7]));

    expect($outcome['status'])->toBe(SupplierPriceList::STATUS_VERIFIED);
});

test('a row OCR split into name, unit and price lines still matches', function () {
    // The text OCR.space returned for a real two-row price-list photo.
    $text = "CLEAN SUPPLY CO.\nLaundry Supplies Price List - November 2026\nContact: Ana Reyes | 0917 123 4567\n"
        ."Prices are subject to change without prior notice.\nITEM\nUNIT\nPRICE (PHP)\n"
        ."Liquid Detergent\ngallon\n450.00\nFabric Softener\ngallon\n380.00";

    $verified = (new PriceListMatcher)->match($text, matcherItems(['Liquid Detergent' => 450, 'Fabric Softener' => 380]));
    $mismatch = (new PriceListMatcher)->match($text, matcherItems(['Liquid Detergent' => 450, 'Fabric Softener' => 400]));

    expect($verified['status'])->toBe(SupplierPriceList::STATUS_VERIFIED)
        ->and($mismatch['status'])->toBe(SupplierPriceList::STATUS_MISMATCH)
        ->and($mismatch['results'][1])
        ->result->toBe(PriceListMatcher::RESULT_PRICE_MISMATCH)
        ->found_price->toEqual(380);
});

test('an item whose row has no price does not take the next item\'s price', function () {
    $outcome = (new PriceListMatcher)->match("Bleach\nbottle\nSoap\nbar\n95.00", matcherItems(['Bleach' => 95]));

    expect($outcome['results'][0]['result'])->toBe(PriceListMatcher::RESULT_PRICE_MISMATCH)
        ->and($outcome['results'][0]['found_price'])->toBeNull();
});
