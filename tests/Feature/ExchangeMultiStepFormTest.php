<?php

use App\Livewire\Exchange\Board;
use App\Livewire\Exchange\CreateRequest;
use App\Models\ExchangeRequest;
use App\Models\User;
use App\Services\ExchangeRateService;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $mock = Mockery::mock(ExchangeRateService::class);
    $mock->shouldReceive('getRate')->andReturn(0.63);
    app()->instance(ExchangeRateService::class, $mock);
});

test('step 1 nextStep advances to step 2 with valid currencies', function () {
    Livewire::test(CreateRequest::class)
        ->set('from_currency', 'NGN')
        ->set('to_currency', 'RWF')
        ->call('nextStep')
        ->assertSet('step', 2)
        ->assertHasNoErrors(['from_currency', 'to_currency']);
});

test('step 1 nextStep fails validation if from_currency is empty', function () {
    Livewire::test(CreateRequest::class)
        ->set('from_currency', '')
        ->set('to_currency', 'RWF')
        ->call('nextStep')
        ->assertSet('step', 1)
        ->assertHasErrors(['from_currency']);
});

test('step 1 nextStep fails validation if to_currency is empty', function () {
    Livewire::test(CreateRequest::class)
        ->set('from_currency', 'NGN')
        ->set('to_currency', '')
        ->call('nextStep')
        ->assertSet('step', 1)
        ->assertHasErrors(['to_currency']);
});

test('step 2 nextStep advances to step 3 with valid amounts and payment methods', function () {
    Livewire::test(CreateRequest::class)
        ->set('step', 2)
        ->set('from_amount', '50000')
        ->set('to_amount', '31500')
        ->set('official_rate', '0.63')
        ->set('offered_rate', '0.63')
        ->set('payment_method_sending', 'bank_transfer')
        ->set('payment_method_receiving', 'mobile_money_mtn')
        ->call('nextStep')
        ->assertSet('step', 3)
        ->assertHasNoErrors();
});

test('step 2 nextStep fails validation if amounts are missing', function () {
    Livewire::test(CreateRequest::class)
        ->set('step', 2)
        ->set('from_amount', '')
        ->set('to_amount', '')
        ->set('official_rate', '0.63')
        ->set('offered_rate', '0.63')
        ->set('payment_method_sending', 'bank_transfer')
        ->set('payment_method_receiving', 'mobile_money_mtn')
        ->call('nextStep')
        ->assertSet('step', 2)
        ->assertHasErrors(['from_amount', 'to_amount']);
});

test('step 2 nextStep fails validation if payment methods are missing', function () {
    Livewire::test(CreateRequest::class)
        ->set('step', 2)
        ->set('from_amount', '50000')
        ->set('to_amount', '31500')
        ->set('official_rate', '0.63')
        ->set('offered_rate', '0.63')
        ->set('payment_method_sending', '')
        ->set('payment_method_receiving', '')
        ->call('nextStep')
        ->assertSet('step', 2)
        ->assertHasErrors(['payment_method_sending', 'payment_method_receiving']);
});

test('prevStep decrements the step', function () {
    Livewire::test(CreateRequest::class)
        ->set('step', 2)
        ->call('prevStep')
        ->assertSet('step', 1);
});

test('prevStep does not go below step 1', function () {
    Livewire::test(CreateRequest::class)
        ->set('step', 1)
        ->call('prevStep')
        ->assertSet('step', 1);
});

test('create on step 3 saves the record and dispatches exchange-request-created', function () {
    Livewire::test(CreateRequest::class)
        ->set('step', 3)
        ->set('from_currency', 'NGN')
        ->set('to_currency', 'RWF')
        ->set('from_amount', '50000')
        ->set('to_amount', '31500')
        ->set('official_rate', '0.63')
        ->set('offered_rate', '0.63')
        ->set('payment_method_sending', 'bank_transfer')
        ->set('payment_method_receiving', 'mobile_money_mtn')
        ->set('expires_in_days', 7)
        ->call('create')
        ->assertDispatched('exchange-request-created');

    expect(ExchangeRequest::count())->toBe(1);
    expect(ExchangeRequest::first()->user_id)->toBe($this->user->id);
});

test('calling create twice only creates one record (double-submit guard)', function () {
    $component = Livewire::test(CreateRequest::class)
        ->set('step', 3)
        ->set('from_currency', 'NGN')
        ->set('to_currency', 'RWF')
        ->set('from_amount', '50000')
        ->set('to_amount', '31500')
        ->set('official_rate', '0.63')
        ->set('offered_rate', '0.63')
        ->set('payment_method_sending', 'bank_transfer')
        ->set('payment_method_receiving', 'mobile_money_mtn')
        ->set('expires_in_days', 7)
        ->call('create');

    // Simulate a second submission while $submitting is true
    $component->set('submitting', true)->call('create');

    expect(ExchangeRequest::count())->toBe(1);
});

test('Board onRequestCreated closes modal and clears requests', function () {
    Livewire::test(Board::class)
        ->set('showCreateModal', true)
        ->dispatch('exchange-request-created')
        ->assertSet('showCreateModal', false);
});
