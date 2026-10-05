<?php

use App\Models\Branch;
use App\Models\Delivery;
use App\Models\Employee;
use App\Models\EmployeeArchive;
use App\Models\Order;
use App\Models\Rider;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\Supplier;
use App\Support\PhilippinePhone;

it('formats supported Philippine mobile number inputs consistently', function (string $input) {
    expect(PhilippinePhone::format($input))->toBe('+63-912-3456-789');
})->with([
    'local format' => '09123456789',
    'country code format' => '639123456789',
    'international format' => '+639123456789',
    'already formatted' => '+63-912-3456-789',
]);

it('provides the compact e164 value for external services', function () {
    expect(PhilippinePhone::e164('+63-912-3456-789'))->toBe('+639123456789');
});

it('rejects non-mobile or incomplete numbers', function (string $input) {
    expect(PhilippinePhone::isValid($input))->toBeFalse();
})->with([
    'landline' => '02-8123-4567',
    'incomplete' => '+63-912-3456',
    'invalid prefix' => '+63-812-3456-789',
]);

it('applies the display format to every phone-bearing model', function (string $model, string $attribute) {
    $record = new $model;
    $record->{$attribute} = '09123456789';

    expect($record->getAttributes()[$attribute])->toBe('+63-912-3456-789')
        ->and($record->{$attribute})->toBe('+63-912-3456-789');
})->with([
    'shop' => [Shop::class, 'phone'],
    'subscription order' => [Order::class, 'phone'],
    'employee' => [Employee::class, 'phone'],
    'employee archive' => [EmployeeArchive::class, 'phone'],
    'branch' => [Branch::class, 'phone'],
    'supplier' => [Supplier::class, 'phone'],
    'rider' => [Rider::class, 'phone'],
    'customer order' => [ShopOrder::class, 'customer_phone'],
    'delivery' => [Delivery::class, 'customer_phone'],
]);
