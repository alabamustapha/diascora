<?php

use App\Models\DeliveryOffer;
use App\Models\DeliveryRequest;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('board is accessible to authenticated users', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('delivery.board'))->assertSuccessful();
});

test('board redirects guests to login', function () {
    $this->get(route('delivery.board'))->assertRedirect(route('login'));
});

test('board shows open requests', function () {
    $user = User::factory()->create();
    DeliveryRequest::factory()->count(3)->create();

    $this->actingAs($user);

    Livewire::test(\App\Livewire\Delivery\Board::class)
        ->assertCount('requests', 3);
});

test('board country filter narrows results', function () {
    $user = User::factory()->create();
    DeliveryRequest::factory()->create(['destination_country' => 'NG']);
    DeliveryRequest::factory()->create(['destination_country' => 'KE']);

    $this->actingAs($user);

    Livewire::test(\App\Livewire\Delivery\Board::class)
        ->set('filterCountry', 'NG')
        ->assertCount('requests', 1);
});

test('my-requests shows only authenticated user requests', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    DeliveryRequest::factory()->count(2)->create(['user_id' => $owner->id]);
    DeliveryRequest::factory()->create(['user_id' => $other->id]);

    $this->actingAs($owner);

    Livewire::test(\App\Livewire\Delivery\MyRequests::class)
        ->assertCount('myRequests', 2);
});

test('my-requests search filters by description', function () {
    $user = User::factory()->create();
    DeliveryRequest::factory()->create(['user_id' => $user->id, 'description' => 'Electronics laptop charger for delivery']);
    DeliveryRequest::factory()->create(['user_id' => $user->id, 'description' => 'Food spices from home market']);

    $this->actingAs($user);

    Livewire::test(\App\Livewire\Delivery\MyRequests::class)
        ->set('search', 'laptop')
        ->assertCount('myRequests', 1);
});

test('my-requests status filter returns matching requests', function () {
    $user = User::factory()->create();
    DeliveryRequest::factory()->create(['user_id' => $user->id, 'status' => 'open']);
    DeliveryRequest::factory()->closed()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(\App\Livewire\Delivery\MyRequests::class)
        ->set('filterStatus', 'closed')
        ->assertCount('myRequests', 1);
});

test('expired filter returns open requests past their expiry date', function () {
    $user = User::factory()->create();
    DeliveryRequest::factory()->expired()->create(['user_id' => $user->id]);
    DeliveryRequest::factory()->create(['user_id' => $user->id, 'status' => 'open', 'expires_at' => now()->addDay()]);

    $this->actingAs($user);

    Livewire::test(\App\Livewire\Delivery\MyRequests::class)
        ->set('filterStatus', 'expired')
        ->assertCount('myRequests', 1);
});

test('owner can close an open request', function () {
    $user = User::factory()->create();
    $request = DeliveryRequest::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(\App\Livewire\Delivery\MyRequests::class)
        ->call('closeRequest', $request->id);

    expect($request->fresh()->status)->toBe('closed');
});

test('non-owner cannot close a request', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $request = DeliveryRequest::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($other);

    Livewire::test(\App\Livewire\Delivery\MyRequests::class)
        ->call('closeRequest', $request->id);

    expect($request->fresh()->status)->toBe('open');
});

test('owner can soft-delete an expired request with no offers', function () {
    $user = User::factory()->create();
    $request = DeliveryRequest::factory()->expired()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(\App\Livewire\Delivery\MyRequests::class)
        ->call('deleteRequest', $request->id);

    expect(DeliveryRequest::find($request->id))->toBeNull()
        ->and(DeliveryRequest::withTrashed()->find($request->id))->not->toBeNull();
});

test('expired request with offers cannot be deleted', function () {
    $user = User::factory()->create();
    $traveler = User::factory()->create();
    $request = DeliveryRequest::factory()->expired()->create(['user_id' => $user->id]);

    DeliveryOffer::factory()->create([
        'delivery_request_id' => $request->id,
        'user_id' => $traveler->id,
    ]);

    $this->actingAs($user);

    Livewire::test(\App\Livewire\Delivery\MyRequests::class)
        ->call('deleteRequest', $request->id);

    expect($request->fresh())->not->toBeNull();
});

test('non-expired open request cannot be deleted', function () {
    $user = User::factory()->create();
    $request = DeliveryRequest::factory()->create([
        'user_id' => $user->id,
        'status' => 'open',
        'expires_at' => now()->addDay(),
    ]);

    $this->actingAs($user);

    Livewire::test(\App\Livewire\Delivery\MyRequests::class)
        ->call('deleteRequest', $request->id);

    expect($request->fresh())->not->toBeNull();
});

test('non-owner cannot delete an expired request', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $request = DeliveryRequest::factory()->expired()->create(['user_id' => $owner->id]);

    $this->actingAs($other);

    Livewire::test(\App\Livewire\Delivery\MyRequests::class)
        ->call('deleteRequest', $request->id);

    expect($request->fresh())->not->toBeNull();
});

test('DeliveryRequest scopeOpen returns only open requests', function () {
    DeliveryRequest::factory()->create(['status' => 'open']);
    DeliveryRequest::factory()->matched()->create();
    DeliveryRequest::factory()->closed()->create();

    expect(DeliveryRequest::query()->open()->count())->toBe(1);
});

test('isOwnedBy returns true for request owner', function () {
    $user = User::factory()->create();
    $request = DeliveryRequest::factory()->create(['user_id' => $user->id]);

    expect($request->isOwnedBy($user))->toBeTrue();
});

test('isOwnedBy returns false for other users', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $request = DeliveryRequest::factory()->create(['user_id' => $owner->id]);

    expect($request->isOwnedBy($other))->toBeFalse();
});

test('imageUrl returns null when no image path set', function () {
    $request = DeliveryRequest::factory()->create(['item_image_path' => null]);

    expect($request->imageUrl())->toBeNull();
});

test('imageUrl returns storage url when image path set', function () {
    Storage::fake('public');

    $request = DeliveryRequest::factory()->withImage()->create();

    expect($request->imageUrl())->not->toBeNull()
        ->and($request->imageUrl())->toContain('delivery-items');
});
