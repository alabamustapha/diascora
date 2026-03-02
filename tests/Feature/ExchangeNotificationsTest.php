<?php

use App\Livewire\Exchange\Board;
use App\Livewire\Exchange\MyRequests;
use App\Livewire\NotificationBell;
use App\Models\ExchangeInterest;
use App\Models\ExchangeRequest;
use App\Models\User;
use App\Notifications\InterestAccepted;
use App\Notifications\InterestReceived;
use App\Notifications\InterestRejected;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('submitting interest notifies the lister', function () {
    Notification::fake();

    $lister = User::factory()->create();
    $peer = User::factory()->create();
    $request = ExchangeRequest::factory()->create(['user_id' => $lister->id]);

    $this->actingAs($peer);

    Livewire::test(Board::class)
        ->call('openInterestModal', $request->id)
        ->set('interestComment', 'I want to exchange')
        ->set('interestPaymentSending', 'bank_transfer')
        ->set('interestPaymentReceiving', 'mobile_money_mtn')
        ->call('submitInterest');

    Notification::assertSentTo($lister, InterestReceived::class);
});

test('submitting interest does not notify the peer themselves', function () {
    Notification::fake();

    $lister = User::factory()->create();
    $peer = User::factory()->create();
    $request = ExchangeRequest::factory()->create(['user_id' => $lister->id]);

    $this->actingAs($peer);

    Livewire::test(Board::class)
        ->call('openInterestModal', $request->id)
        ->set('interestComment', 'I want to exchange')
        ->set('interestPaymentSending', 'bank_transfer')
        ->set('interestPaymentReceiving', 'mobile_money_mtn')
        ->call('submitInterest');

    Notification::assertNotSentTo($peer, InterestReceived::class);
});

test('accepting an interest notifies the accepted party', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $peer = User::factory()->create();
    $request = ExchangeRequest::factory()->create(['user_id' => $owner->id]);
    $interest = ExchangeInterest::factory()->create([
        'exchange_request_id' => $request->id,
        'user_id' => $peer->id,
    ]);

    $this->actingAs($owner);

    Livewire::test(MyRequests::class)->call('acceptInterest', $interest->id);

    Notification::assertSentTo($peer, InterestAccepted::class);
});

test('accepting one interest sends InterestRejected to all others but not the accepted party', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $accepted = User::factory()->create();
    $rejected1 = User::factory()->create();
    $rejected2 = User::factory()->create();
    $request = ExchangeRequest::factory()->create(['user_id' => $owner->id]);

    $acceptedInterest = ExchangeInterest::factory()->create([
        'exchange_request_id' => $request->id,
        'user_id' => $accepted->id,
    ]);

    ExchangeInterest::factory()->create([
        'exchange_request_id' => $request->id,
        'user_id' => $rejected1->id,
    ]);

    ExchangeInterest::factory()->create([
        'exchange_request_id' => $request->id,
        'user_id' => $rejected2->id,
    ]);

    $this->actingAs($owner);

    Livewire::test(MyRequests::class)->call('acceptInterest', $acceptedInterest->id);

    Notification::assertSentTo($rejected1, InterestRejected::class);
    Notification::assertSentTo($rejected2, InterestRejected::class);
    Notification::assertNotSentTo($accepted, InterestRejected::class);
});

test('owner does not receive InterestAccepted or InterestRejected notifications', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $peer = User::factory()->create();
    $request = ExchangeRequest::factory()->create(['user_id' => $owner->id]);
    $interest = ExchangeInterest::factory()->create([
        'exchange_request_id' => $request->id,
        'user_id' => $peer->id,
    ]);

    $this->actingAs($owner);

    Livewire::test(MyRequests::class)->call('acceptInterest', $interest->id);

    Notification::assertNotSentTo($owner, InterestAccepted::class);
    Notification::assertNotSentTo($owner, InterestRejected::class);
});

test('notification bell shows correct unread count', function () {
    $user = User::factory()->create();
    $request = ExchangeRequest::factory()->create(['user_id' => $user->id]);
    $interest = ExchangeInterest::factory()->create([
        'exchange_request_id' => $request->id,
        'user_id' => User::factory()->create()->id,
    ]);

    $user->notify(new InterestReceived($interest));
    $user->notify(new InterestReceived($interest));

    $this->actingAs($user);

    Livewire::test(NotificationBell::class)
        ->assertSet('unreadCount', 2);
});

test('markAllRead clears unread notifications', function () {
    $user = User::factory()->create();
    $request = ExchangeRequest::factory()->create(['user_id' => $user->id]);
    $interest = ExchangeInterest::factory()->create([
        'exchange_request_id' => $request->id,
        'user_id' => User::factory()->create()->id,
    ]);

    $user->notify(new InterestReceived($interest));
    $user->notify(new InterestReceived($interest));

    $this->actingAs($user);

    Livewire::test(NotificationBell::class)
        ->assertSet('unreadCount', 2)
        ->call('markAllRead')
        ->assertSet('unreadCount', 0);
});
