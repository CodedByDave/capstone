<?php

use App\Enums\AccountType;
use App\Http\Requests\Shop\Operations\StoreShopOrderRequest;
use App\Http\Requests\Shop\Operations\UpdateShopOrderRequest;
use App\Models\Shop;
use App\Models\User;

function alternateContactErrors(string $requestClass, array $input): Illuminate\Support\MessageBag
{
    // The rules look up the acting shop (for the promotion check), so the request needs a shop owner.
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Contact Laundry '.$owner->id,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
    ]);

    $request = new $requestClass;
    $request->setUserResolver(fn () => $owner);
    $request->merge($input);

    return validator($request->all(), $request->rules(), $request->messages())->errors();
}

dataset('order requests', [
    'staff create' => [StoreShopOrderRequest::class],
    'staff edit' => [UpdateShopOrderRequest::class],
]);

it('asks for the alternate contact number when only the name is given', function (string $class) {
    $errors = alternateContactErrors($class, ['alternate_contact_name' => 'Aling Nena']);

    expect($errors->first('alternate_contact_phone'))->toBe("Enter the alternate contact's mobile number.");
})->with('order requests');

it('asks for the alternate contact name when only the number is given', function (string $class) {
    $errors = alternateContactErrors($class, ['alternate_contact_phone' => '09171234567']);

    expect($errors->first('alternate_contact_name'))->toBe('Enter the name of the alternate contact.');
})->with('order requests');

it('accepts a name with a valid mobile number, or neither on a walk-in order', function (string $class) {
    expect(alternateContactErrors($class, [
        'alternate_contact_name' => 'Aling Nena',
        'alternate_contact_phone' => '09171234567',
    ])->keys())->not->toContain('alternate_contact_name', 'alternate_contact_phone');

    expect(alternateContactErrors($class, [])->keys())->not->toContain('alternate_contact_name', 'alternate_contact_phone');
})->with('order requests');

it('rejects an alternate contact number that is not a Philippine mobile number', function (string $class) {
    $errors = alternateContactErrors($class, [
        'alternate_contact_name' => 'Aling Nena',
        'alternate_contact_phone' => '12345',
    ]);

    expect($errors->has('alternate_contact_phone'))->toBeTrue();
})->with('order requests');

it('requires the phone number and address for a delivery pickup order only', function (string $class) {
    $pickup = alternateContactErrors($class, ['pickup_type' => 'pickup']);

    expect($pickup->first('customer_phone'))->toBe("Enter the customer's mobile number so the rider can reach them.")
        ->and($pickup->first('customer_address'))->toBe('Enter the address the rider should go to.');

    $walkIn = alternateContactErrors($class, ['pickup_type' => 'walk_in']);

    expect($walkIn->keys())->not->toContain('customer_phone', 'customer_address');

    $complete = alternateContactErrors($class, [
        'pickup_type' => 'pickup',
        'customer_phone' => '09171234567',
        'customer_address' => '12 Rizal St, Imus',
    ]);

    expect($complete->keys())->not->toContain('customer_phone', 'customer_address');
})->with('order requests');

it('requires the alternate contact name and number on a delivery pickup order only', function (string $class) {
    $pickup = alternateContactErrors($class, ['pickup_type' => 'pickup']);

    expect($pickup->first('alternate_contact_name'))->toBe('Enter the name of the alternate contact.')
        ->and($pickup->first('alternate_contact_phone'))->toBe("Enter the alternate contact's mobile number.");

    $walkIn = alternateContactErrors($class, ['pickup_type' => 'walk_in']);

    expect($walkIn->keys())->not->toContain('alternate_contact_name', 'alternate_contact_phone');
})->with('order requests');

it('requires the contact details when the finished laundry is delivered to the customer', function (string $class) {
    $delivery = alternateContactErrors($class, ['pickup_type' => 'walk_in', 'deliver_to_customer' => true]);

    expect($delivery->first('customer_phone'))->toBe("Enter the customer's mobile number so the rider can reach them.")
        ->and($delivery->first('customer_address'))->toBe('Enter the address the rider should go to.')
        ->and($delivery->first('alternate_contact_name'))->toBe('Enter the name of the alternate contact.')
        ->and($delivery->first('alternate_contact_phone'))->toBe("Enter the alternate contact's mobile number.");

    $collected = alternateContactErrors($class, ['pickup_type' => 'walk_in', 'deliver_to_customer' => false]);

    expect($collected->keys())->not->toContain('customer_phone', 'customer_address', 'alternate_contact_name', 'alternate_contact_phone');
})->with('order requests');
