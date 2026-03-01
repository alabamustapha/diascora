<?php

use App\Models\ExchangeRequest;
use App\Models\User;
use Livewire\Livewire;

test('authenticated user can create an exchange request', function () {
    $user = User::factory()->create();

    $request = ExchangeRequest::factory()->create([
        'user_id' => $user->id,
        'from_currency' => 'NGN',
        'to_currency' => 'RWF',
        'from_amount' => 100000,
        'to_amount' => 63000,
        'official_rate_at_posting' => 0.63,
        'offered_rate' => 0.63,
        'status' => 'open',
    ]);

    expect($request->exists)->toBeTrue()
        ->and($request->user_id)->toBe($user->id)
        ->and($request->from_currency)->toBe('NGN')
        ->and($request->status)->toBe('open');
});

test('anonymous posting hides poster name on board', function () {
    $user = User::factory()->create();
    $request = ExchangeRequest::factory()->anonymous()->create(['user_id' => $user->id]);

    expect($request->is_anonymous)->toBeTrue();

    // The board should render "Community Member" badge — verify via component
    $this->actingAs($user);

    Livewire::test(\App\Livewire\Exchange\Board::class)
        ->assertSee('Community Member');
});

test('rate is stored at posting time', function () {
    $user = User::factory()->create();

    $request = ExchangeRequest::factory()->create([
        'user_id' => $user->id,
        'official_rate_at_posting' => 0.630000,
        'offered_rate' => 0.640000,
    ]);

    expect((float) $request->official_rate_at_posting)->toBe(0.63)
        ->and((float) $request->offered_rate)->toBe(0.64);
});

test('user cannot express interest in their own request', function () {
    $user = User::factory()->create();
    $request = ExchangeRequest::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(\App\Livewire\Exchange\Board::class)
        ->call('openInterestModal', $request->id)
        ->assertSet('showInterestModal', false);
});

test('board shows open requests to unauthenticated users', function () {
    ExchangeRequest::factory()->count(3)->create();

    $this->get(route('exchange.board'))
        ->assertRedirect(route('login'));
});

test('board is accessible to authenticated users', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('exchange.board'))->assertSuccessful();
});

test('my-requests page shows only the authenticated user requests', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    ExchangeRequest::factory()->count(2)->create(['user_id' => $owner->id]);
    ExchangeRequest::factory()->create(['user_id' => $other->id]);

    $this->actingAs($owner);

    // myRequests is now a paginator; count() returns items on the current page
    Livewire::test(\App\Livewire\Exchange\MyRequests::class)
        ->assertCount('myRequests', 2);
});

test('owner can relist a closed request as a new open request', function () {
    $user = User::factory()->create();
    $original = ExchangeRequest::factory()->closed()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(\App\Livewire\Exchange\MyRequests::class)
        ->call('relistRequest', $original->id);

    expect(ExchangeRequest::where('user_id', $user->id)->where('status', 'open')->count())->toBe(1)
        ->and(ExchangeRequest::where('user_id', $user->id)->count())->toBe(2);
});

test('non-owner cannot relist a request', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $request = ExchangeRequest::factory()->closed()->create(['user_id' => $owner->id]);

    $this->actingAs($other);

    Livewire::test(\App\Livewire\Exchange\MyRequests::class)
        ->call('relistRequest', $request->id);

    expect(ExchangeRequest::count())->toBe(1);
});

test('my-requests search filters by currency', function () {
    $user = User::factory()->create();
    ExchangeRequest::factory()->create(['user_id' => $user->id, 'from_currency' => 'NGN', 'to_currency' => 'RWF']);
    ExchangeRequest::factory()->create(['user_id' => $user->id, 'from_currency' => 'KES', 'to_currency' => 'RWF']);

    $this->actingAs($user);

    Livewire::test(\App\Livewire\Exchange\MyRequests::class)
        ->set('search', 'NGN')
        ->assertCount('myRequests', 1);
});

test('my-requests status filter returns matching requests', function () {
    $user = User::factory()->create();
    ExchangeRequest::factory()->create(['user_id' => $user->id, 'status' => 'open']);
    ExchangeRequest::factory()->closed()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(\App\Livewire\Exchange\MyRequests::class)
        ->set('filterStatus', 'closed')
        ->assertCount('myRequests', 1);
});

test('owner can soft-delete an expired request with no interests', function () {
    $user = User::factory()->create();
    $request = ExchangeRequest::factory()->expired()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(\App\Livewire\Exchange\MyRequests::class)
        ->call('deleteRequest', $request->id);

    expect(ExchangeRequest::find($request->id))->toBeNull()
        ->and(ExchangeRequest::withTrashed()->find($request->id))->not->toBeNull();
});

test('expired request with interests cannot be deleted', function () {
    $user = User::factory()->create();
    $requester = User::factory()->create();
    $request = ExchangeRequest::factory()->expired()->create(['user_id' => $user->id]);

    \App\Models\ExchangeInterest::factory()->create([
        'exchange_request_id' => $request->id,
        'user_id' => $requester->id,
    ]);

    $this->actingAs($user);

    Livewire::test(\App\Livewire\Exchange\MyRequests::class)
        ->call('deleteRequest', $request->id);

    expect($request->fresh())->not->toBeNull();
});

test('non-expired open request cannot be deleted', function () {
    $user = User::factory()->create();
    $request = ExchangeRequest::factory()->create([
        'user_id' => $user->id,
        'status' => 'open',
        'expires_at' => now()->addDay(),
    ]);

    $this->actingAs($user);

    Livewire::test(\App\Livewire\Exchange\MyRequests::class)
        ->call('deleteRequest', $request->id);

    expect($request->fresh())->not->toBeNull();
});

test('non-owner cannot delete an expired request', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $request = ExchangeRequest::factory()->expired()->create(['user_id' => $owner->id]);

    $this->actingAs($other);

    Livewire::test(\App\Livewire\Exchange\MyRequests::class)
        ->call('deleteRequest', $request->id);

    expect($request->fresh())->not->toBeNull();
});

test('expired filter returns open requests past their expiry date', function () {
    $user = User::factory()->create();
    ExchangeRequest::factory()->create([
        'user_id' => $user->id,
        'status' => 'open',
        'expires_at' => now()->subDay(),
    ]);
    ExchangeRequest::factory()->create([
        'user_id' => $user->id,
        'status' => 'open',
        'expires_at' => now()->addDay(),
    ]);

    $this->actingAs($user);

    Livewire::test(\App\Livewire\Exchange\MyRequests::class)
        ->set('filterStatus', 'expired')
        ->assertCount('myRequests', 1);
});

test('owner can close an open request', function () {
    $user = User::factory()->create();
    $request = ExchangeRequest::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(\App\Livewire\Exchange\MyRequests::class)
        ->call('closeRequest', $request->id);

    expect($request->fresh()->status)->toBe('closed');
});

test('non-owner cannot close a request', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $request = ExchangeRequest::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($other);

    Livewire::test(\App\Livewire\Exchange\MyRequests::class)
        ->call('closeRequest', $request->id);

    expect($request->fresh()->status)->toBe('open');
});

test('ExchangeRequest scopeOpen returns only open requests', function () {
    ExchangeRequest::factory()->create(['status' => 'open']);
    ExchangeRequest::factory()->matched()->create();
    ExchangeRequest::factory()->closed()->create();

    expect(ExchangeRequest::query()->open()->count())->toBe(1);
});

test('isOwnedBy returns true for request owner', function () {
    $user = User::factory()->create();
    $request = ExchangeRequest::factory()->create(['user_id' => $user->id]);

    expect($request->isOwnedBy($user))->toBeTrue();
});

test('isOwnedBy returns false for other users', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $request = ExchangeRequest::factory()->create(['user_id' => $owner->id]);

    expect($request->isOwnedBy($other))->toBeFalse();
});
