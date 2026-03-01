<?php

use App\Models\ExchangeInterest;
use App\Models\ExchangeRequest;
use App\Models\User;
use Livewire\Livewire;

test('peer can express interest in a request with comment and payment methods', function () {
    $owner = User::factory()->create();
    $peer = User::factory()->create();
    $request = ExchangeRequest::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($peer);

    Livewire::test(\App\Livewire\Exchange\Board::class)
        ->call('openInterestModal', $request->id)
        ->assertSet('showInterestModal', true)
        ->set('interestComment', 'I want to exchange money')
        ->set('interestPaymentSending', 'bank_transfer')
        ->set('interestPaymentReceiving', 'mobile_money_mtn')
        ->call('submitInterest');

    expect(ExchangeInterest::query()->where('exchange_request_id', $request->id)->count())->toBe(1);

    $interest = ExchangeInterest::query()->first();
    expect($interest->comment)->toBe('I want to exchange money')
        ->and($interest->payment_method_sending)->toBe('bank_transfer')
        ->and($interest->status)->toBe('pending');
});

test('user cannot express interest in their own request', function () {
    $user = User::factory()->create();
    $request = ExchangeRequest::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(\App\Livewire\Exchange\Board::class)
        ->call('openInterestModal', $request->id)
        ->assertSet('showInterestModal', false);

    expect(ExchangeInterest::query()->count())->toBe(0);
});

test('user cannot express interest in the same request twice', function () {
    $owner = User::factory()->create();
    $peer = User::factory()->create();
    $request = ExchangeRequest::factory()->create(['user_id' => $owner->id]);

    ExchangeInterest::factory()->create([
        'exchange_request_id' => $request->id,
        'user_id' => $peer->id,
    ]);

    $this->actingAs($peer);

    $this->expectException(\Illuminate\Database\QueryException::class);

    ExchangeInterest::create([
        'exchange_request_id' => $request->id,
        'user_id' => $peer->id,
        'comment' => 'Second attempt',
        'payment_method_sending' => 'cash',
        'payment_method_receiving' => 'cash',
        'status' => 'pending',
    ]);
});

test('accepting an interest sets request as matched and reveals contact', function () {
    $owner = User::factory()->create();
    $peer = User::factory()->create(['phone_number' => '+25070000001', 'whatsapp_enabled' => true]);
    $request = ExchangeRequest::factory()->create(['user_id' => $owner->id]);
    $interest = ExchangeInterest::factory()->create([
        'exchange_request_id' => $request->id,
        'user_id' => $peer->id,
    ]);

    $this->actingAs($owner);

    Livewire::test(\App\Livewire\Exchange\MyRequests::class)
        ->call('acceptInterest', $interest->id);

    $request->refresh();
    $interest->refresh();

    expect($request->status)->toBe('matched')
        ->and($request->accepted_interest_id)->toBe($interest->id)
        ->and($interest->status)->toBe('accepted');
});

test('accepting one interest rejects all others', function () {
    $owner = User::factory()->create();
    $peer1 = User::factory()->create();
    $peer2 = User::factory()->create();
    $peer3 = User::factory()->create();
    $request = ExchangeRequest::factory()->create(['user_id' => $owner->id]);

    $interest1 = ExchangeInterest::factory()->create(['exchange_request_id' => $request->id, 'user_id' => $peer1->id]);
    $interest2 = ExchangeInterest::factory()->create(['exchange_request_id' => $request->id, 'user_id' => $peer2->id]);
    $interest3 = ExchangeInterest::factory()->create(['exchange_request_id' => $request->id, 'user_id' => $peer3->id]);

    $this->actingAs($owner);

    Livewire::test(\App\Livewire\Exchange\MyRequests::class)
        ->call('acceptInterest', $interest1->id);

    expect($interest1->fresh()->status)->toBe('accepted')
        ->and($interest2->fresh()->status)->toBe('rejected')
        ->and($interest3->fresh()->status)->toBe('rejected');
});

test('contact is revealed to both owner and accepted peer after match', function () {
    $owner = User::factory()->create();
    $peer = User::factory()->create();
    $request = ExchangeRequest::factory()->matched()->create(['user_id' => $owner->id]);
    $interest = ExchangeInterest::factory()->accepted()->create([
        'exchange_request_id' => $request->id,
        'user_id' => $peer->id,
    ]);
    $request->update(['accepted_interest_id' => $interest->id]);
    $request->refresh();

    // Owner can see contact
    $this->actingAs($owner);
    $component = Livewire::test(\App\Livewire\Exchange\MyRequests::class);
    expect($component->instance()->canSeeContact($request))->toBeTrue();

    // Peer can see contact
    $this->actingAs($peer);
    $peerComponent = Livewire::test(\App\Livewire\Exchange\MyRequests::class);
    expect($peerComponent->instance()->canSeeContact($request))->toBeTrue();
});

test('non-participant cannot see contact after match', function () {
    $owner = User::factory()->create();
    $peer = User::factory()->create();
    $stranger = User::factory()->create();
    $request = ExchangeRequest::factory()->matched()->create(['user_id' => $owner->id]);
    $interest = ExchangeInterest::factory()->accepted()->create([
        'exchange_request_id' => $request->id,
        'user_id' => $peer->id,
    ]);
    $request->update(['accepted_interest_id' => $interest->id]);
    $request->refresh();

    $this->actingAs($stranger);
    $component = Livewire::test(\App\Livewire\Exchange\MyRequests::class);
    expect($component->instance()->canSeeContact($request))->toBeFalse();
});
