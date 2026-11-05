<?php

use App\Enums\AccountType;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('a driver uploads a profile photo and it shows on their profile', function () {
    Storage::fake('public');
    $rider = onlineRider(driverShop());
    $user = $rider->employee->user;

    $this->actingAs($user)
        ->post('/profile/photo', ['photo' => UploadedFile::fake()->image('me.jpg')])
        ->assertRedirect();

    $path = $user->refresh()->profile_photo_path;
    expect($path)->not->toBeNull();
    Storage::disk('public')->assertExists($path);

    $this->actingAs($user)->get('/driver/profile')
        ->assertInertia(fn (Assert $page) => $page
            ->component('driver/Profile')
            ->where('driver.avatar', $user->profilePhotoUrl()));
});

test('uploading a new photo replaces and deletes the old one', function () {
    Storage::fake('public');
    $user = onlineRider(driverShop())->employee->user;

    $this->actingAs($user)->post('/profile/photo', ['photo' => UploadedFile::fake()->image('a.jpg')]);
    $old = $user->refresh()->profile_photo_path;

    $this->actingAs($user)->post('/profile/photo', ['photo' => UploadedFile::fake()->image('b.png')]);

    Storage::disk('public')->assertMissing($old);
    Storage::disk('public')->assertExists($user->refresh()->profile_photo_path);
});

test('the photo must be an image under 4 MB', function () {
    Storage::fake('public');
    $user = onlineRider(driverShop())->employee->user;

    $this->actingAs($user)->post('/profile/photo', ['photo' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')])
        ->assertSessionHasErrors('photo');
    $this->actingAs($user)->post('/profile/photo', ['photo' => UploadedFile::fake()->image('big.jpg')->size(5000)])
        ->assertSessionHasErrors('photo');
});

test('customers and guests cannot upload through the staff route', function () {
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $photo = UploadedFile::fake()->image('me.jpg');

    $this->post('/profile/photo', ['photo' => $photo])->assertRedirect();
    $this->actingAs($customer)->post('/profile/photo', ['photo' => $photo])->assertRedirect();

    expect($customer->refresh()->profile_photo_path)->toBeNull();
});
