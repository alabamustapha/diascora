<?php

use App\Models\DeliveryOffer;
use App\Models\DeliveryRequest;
use App\Models\User;
use App\Notifications\DeliveryOfferAccepted;
use App\Notifications\DeliveryOfferReceived;
use App\Notifications\DeliveryOfferRejected;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('traveler can offer to carry a package', function () {
    $owner = User::factory()->create();
    $traveler = User::factory()->create();
    $request = DeliveryRequest::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($traveler);

    Livewire::test(\App\Livewire\Delivery\Board::class)
        ->call('openOfferModal', $request->id)
        ->assertSet('showOfferModal', true)
        ->set('offerMessage', 'I am traveling to Nigeria next week and can carry your package')
        ->call('submitOffer');

    expect(DeliveryOffer::where('delivery_request_id', $request->id)->count())->toBe(1);

    $offer = DeliveryOffer::first();
    expect($offer->message)->toBe('I am traveling to Nigeria next week and can carry your package')
        ->and($offer->status)->toBe('pending');
});

test('user cannot offer on their own request', function () {
    $user = User::factory()->create();
    $request = DeliveryRequest::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(\App\Livewire\Delivery\Board::class)
        ->call('openOfferModal', $request->id)
        ->assertSet('showOfferModal', false);

    expect(DeliveryOffer::count())->toBe(0);
});

test('user cannot offer on the same request twice', function () {
    $owner = User::factory()->create();
    $traveler = User::factory()->create();
    $request = DeliveryRequest::factory()->create(['user_id' => $owner->id]);

    DeliveryOffer::factory()->create([
        'delivery_request_id' => $request->id,
        'user_id' => $traveler->id,
    ]);

    $this->actingAs($traveler);

    $this->expectException(\Illuminate\Database\QueryException::class);

    DeliveryOffer::create([
        'delivery_request_id' => $request->id,
        'user_id' => $traveler->id,
        'message' => 'Second attempt',
        'status' => 'pending',
    ]);
});

test('accepting an offer sets request as matched', function () {
    $owner = User::factory()->create();
    $traveler = User::factory()->create(['phone_number' => '+25070000001', 'whatsapp_enabled' => true]);
    $request = DeliveryRequest::factory()->create(['user_id' => $owner->id]);
    $offer = DeliveryOffer::factory()->create([
        'delivery_request_id' => $request->id,
        'user_id' => $traveler->id,
    ]);

    $this->actingAs($owner);

    Livewire::test(\App\Livewire\Delivery\MyRequests::class)
        ->call('acceptOffer', $offer->id);

    $request->refresh();
    $offer->refresh();

    expect($request->status)->toBe('matched')
        ->and($request->accepted_offer_id)->toBe($offer->id)
        ->and($offer->status)->toBe('accepted');
});

test('accepting one offer rejects all others', function () {
    $owner = User::factory()->create();
    $t1 = User::factory()->create();
    $t2 = User::factory()->create();
    $t3 = User::factory()->create();
    $request = DeliveryRequest::factory()->create(['user_id' => $owner->id]);

    $offer1 = DeliveryOffer::factory()->create(['delivery_request_id' => $request->id, 'user_id' => $t1->id]);
    $offer2 = DeliveryOffer::factory()->create(['delivery_request_id' => $request->id, 'user_id' => $t2->id]);
    $offer3 = DeliveryOffer::factory()->create(['delivery_request_id' => $request->id, 'user_id' => $t3->id]);

    $this->actingAs($owner);

    Livewire::test(\App\Livewire\Delivery\MyRequests::class)
        ->call('acceptOffer', $offer1->id);

    expect($offer1->fresh()->status)->toBe('accepted')
        ->and($offer2->fresh()->status)->toBe('rejected')
        ->and($offer3->fresh()->status)->toBe('rejected');
});

test('contact is revealed to both owner and accepted traveler after match', function () {
    $owner = User::factory()->create();
    $traveler = User::factory()->create();
    $request = DeliveryRequest::factory()->matched()->create(['user_id' => $owner->id]);
    $offer = DeliveryOffer::factory()->accepted()->create([
        'delivery_request_id' => $request->id,
        'user_id' => $traveler->id,
    ]);
    $request->update(['accepted_offer_id' => $offer->id]);
    $request->refresh();

    $this->actingAs($owner);
    $component = Livewire::test(\App\Livewire\Delivery\MyRequests::class);
    expect($component->instance()->canSeeContact($request))->toBeTrue();

    $this->actingAs($traveler);
    $peerComponent = Livewire::test(\App\Livewire\Delivery\MyRequests::class);
    expect($peerComponent->instance()->canSeeContact($request))->toBeTrue();
});

test('non-participant cannot see contact after match', function () {
    $owner = User::factory()->create();
    $traveler = User::factory()->create();
    $stranger = User::factory()->create();
    $request = DeliveryRequest::factory()->matched()->create(['user_id' => $owner->id]);
    $offer = DeliveryOffer::factory()->accepted()->create([
        'delivery_request_id' => $request->id,
        'user_id' => $traveler->id,
    ]);
    $request->update(['accepted_offer_id' => $offer->id]);
    $request->refresh();

    $this->actingAs($stranger);
    $component = Livewire::test(\App\Livewire\Delivery\MyRequests::class);
    expect($component->instance()->canSeeContact($request))->toBeFalse();
});

test('DeliveryOfferReceived notification is sent to request owner when offer submitted', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $traveler = User::factory()->create();
    $request = DeliveryRequest::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($traveler);

    Livewire::test(\App\Livewire\Delivery\Board::class)
        ->call('openOfferModal', $request->id)
        ->set('offerMessage', 'I am traveling next week and can carry your package')
        ->call('submitOffer');

    Notification::assertSentTo($owner, DeliveryOfferReceived::class);
    Notification::assertNotSentTo($traveler, DeliveryOfferReceived::class);
});

test('DeliveryOfferAccepted notification is sent to traveler and DeliveryOfferRejected to others', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $t1 = User::factory()->create();
    $t2 = User::factory()->create();
    $request = DeliveryRequest::factory()->create(['user_id' => $owner->id]);
    $offer1 = DeliveryOffer::factory()->create(['delivery_request_id' => $request->id, 'user_id' => $t1->id]);
    DeliveryOffer::factory()->create(['delivery_request_id' => $request->id, 'user_id' => $t2->id]);

    $this->actingAs($owner);

    Livewire::test(\App\Livewire\Delivery\MyRequests::class)
        ->call('acceptOffer', $offer1->id);

    Notification::assertSentTo($t1, DeliveryOfferAccepted::class);
    Notification::assertSentTo($t2, DeliveryOfferRejected::class);
    Notification::assertNotSentTo($owner, DeliveryOfferAccepted::class);
});
