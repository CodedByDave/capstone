<?php

use App\Enums\AccountType;
use App\Models\CustomerAddress;
use App\Models\Shop;
use App\Models\User;
use App\Services\CustomerAddressService;
use Inertia\Testing\AssertableInertia as Assert;

function addrCustomer(): User
{
    return User::factory()->create(['role' => AccountType::Customer->value]);
}

function addrPayload(array $overrides = []): array
{
    return array_merge([
        'label' => 'home',
        'recipient_name' => 'Juan Dela Cruz',
        'phone' => '09171234567',
        'address' => '12 Rizal St, Poblacion, Imus, Cavite',
        'latitude' => 14.4296,
        'longitude' => 120.9367,
    ], $overrides);
}

test('the address page lists only the signed-in customer\'s addresses, default first', function () {
    $me = addrCustomer();
    CustomerAddress::factory()->for($me)->create(['address' => 'Second address, Imus']);
    CustomerAddress::factory()->for($me)->default()->create(['address' => 'Default address, Imus']);
    CustomerAddress::factory()->create(['address' => 'Someone else, Bacoor']);

    $this->actingAs($me)
        ->get(route('user.addresses.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('user/Addresses')
            ->where('limit', CustomerAddressService::MAX_ADDRESSES)
            ->has('addresses', 2)
            ->where('addresses.0.address', 'Default address, Imus')
            ->where('addresses.0.is_default', true)
            ->missing('addresses.0.user_id'));
});

test('the first saved address becomes the default', function () {
    $me = addrCustomer();

    $this->actingAs($me)->post(route('user.addresses.store'), addrPayload())->assertRedirect();

    $address = $me->addresses()->first();
    expect($address->is_default)->toBeTrue()
        ->and($address->public_id)->toHaveLength(26);
});

test('a second address is not the default unless asked for, and asking moves the default', function () {
    $me = addrCustomer();
    $this->actingAs($me)->post(route('user.addresses.store'), addrPayload(['address' => 'First home address']));
    $this->actingAs($me)->post(route('user.addresses.store'), addrPayload(['label' => 'work', 'address' => 'Second work address']));

    expect($me->addresses()->where('is_default', true)->pluck('address')->all())->toBe(['First home address']);

    $this->actingAs($me)->post(route('user.addresses.store'), addrPayload()); // third, rejected
    $work = $me->addresses()->where('label', 'work')->first();

    $this->actingAs($me)->post(route('user.addresses.default', $work->public_id))->assertRedirect();

    expect($me->addresses()->where('is_default', true)->pluck('address')->all())->toBe(['Second work address']);
});

test('a customer cannot save more than two addresses', function () {
    $me = addrCustomer();
    CustomerAddress::factory()->for($me)->default()->create();
    CustomerAddress::factory()->for($me)->create();

    $this->actingAs($me)
        ->post(route('user.addresses.store'), addrPayload())
        ->assertSessionHasErrors('address');

    expect($me->addresses()->count())->toBe(2);
});

test('saving a new default address replaces the previous default', function () {
    $me = addrCustomer();
    $old = CustomerAddress::factory()->for($me)->default()->create();

    $this->actingAs($me)
        ->post(route('user.addresses.store'), addrPayload(['is_default' => true, 'address' => 'The new default address']))
        ->assertRedirect();

    expect($old->refresh()->is_default)->toBeFalse()
        ->and($me->addresses()->where('is_default', true)->count())->toBe(1)
        ->and($me->addresses()->where('is_default', true)->value('address'))->toBe('The new default address');
});

test('an address can be edited', function () {
    $me = addrCustomer();
    $address = CustomerAddress::factory()->for($me)->default()->create();

    $this->actingAs($me)
        ->put(route('user.addresses.update', $address->public_id), addrPayload(['label' => 'work', 'address' => 'Updated address in Bacoor']))
        ->assertRedirect();

    expect($address->refresh())
        ->label->toBe('work')
        ->address->toBe('Updated address in Bacoor')
        ->is_default->toBeTrue();
});

test('deleting the default address promotes the remaining one', function () {
    $me = addrCustomer();
    $default = CustomerAddress::factory()->for($me)->default()->create();
    $other = CustomerAddress::factory()->for($me)->create();

    $this->actingAs($me)->delete(route('user.addresses.destroy', $default->public_id))->assertRedirect();

    expect(CustomerAddress::find($default->id))->toBeNull()
        ->and($other->refresh()->is_default)->toBeTrue();
});

test('one customer cannot read, change, default or delete another customer\'s address', function () {
    $me = addrCustomer();
    $theirs = CustomerAddress::factory()->default()->create();

    $this->actingAs($me)->put(route('user.addresses.update', $theirs->public_id), addrPayload())->assertNotFound();
    $this->actingAs($me)->post(route('user.addresses.default', $theirs->public_id))->assertNotFound();
    $this->actingAs($me)->delete(route('user.addresses.destroy', $theirs->public_id))->assertNotFound();

    expect($theirs->refresh()->address)->not->toBe(addrPayload()['address']);
    expect(CustomerAddress::find($theirs->id))->not->toBeNull();
});

test('addresses are validated', function () {
    $me = addrCustomer();

    $this->actingAs($me)
        ->post(route('user.addresses.store'), addrPayload(['phone' => '123', 'label' => 'castle', 'address' => 'abc', 'latitude' => 14.4, 'longitude' => null]))
        ->assertSessionHasErrors(['phone', 'label', 'address', 'longitude']);

    expect($me->addresses()->count())->toBe(0);
});

test('the exact pin is required', function () {
    $me = addrCustomer();

    $this->actingAs($me)
        ->post(route('user.addresses.store'), addrPayload(['latitude' => null, 'longitude' => null]))
        ->assertSessionHasErrors(['latitude', 'longitude']);

    expect($me->addresses()->count())->toBe(0);
});

test('shop owners cannot use the customer address book', function () {
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    $this->actingAs($owner)->post(route('user.addresses.store'), addrPayload())->assertRedirect();

    expect(CustomerAddress::count())->toBe(0);
});

test('guests are sent to log in', function () {
    $this->get(route('user.addresses.index'))->assertRedirect(route('login'));
});

test('the shop page offers saved addresses of the signed-in customer for the pickup order', function () {
    $me = addrCustomer();
    CustomerAddress::factory()->for($me)->default()->create(['address' => 'Saved pickup spot']);
    CustomerAddress::factory()->create(['address' => 'Not mine']);

    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Address Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'location_mode' => 'single',
        'setup_completed_at' => now(),
    ]);

    $this->actingAs($me)
        ->get(route('user.shops.show', $shop->id))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('user/ShopDetail')
            ->has('savedAddresses', 1)
            ->where('savedAddresses.0.address', 'Saved pickup spot'));
});
