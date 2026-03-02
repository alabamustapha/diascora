<?php

use App\Livewire\Delivery\Board;
use App\Livewire\Delivery\CreateRequest;
use App\Models\DeliveryRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('step 1 nextStep advances to step 2 with valid data', function () {
    Livewire::test(CreateRequest::class)
        ->set('destination_country', 'NG')
        ->set('weight_kg', '2.5')
        ->set('payment_amount', '50')
        ->set('payment_currency', 'NGN')
        ->set('payment_method', 'bank_transfer')
        ->call('nextStep')
        ->assertSet('step', 2)
        ->assertHasNoErrors();
});

test('step 1 nextStep fails if destination_country is empty', function () {
    Livewire::test(CreateRequest::class)
        ->set('destination_country', '')
        ->set('weight_kg', '2.5')
        ->set('payment_amount', '50')
        ->set('payment_currency', 'NGN')
        ->set('payment_method', 'bank_transfer')
        ->call('nextStep')
        ->assertSet('step', 1)
        ->assertHasErrors(['destination_country']);
});

test('step 1 nextStep fails if weight_kg is missing', function () {
    Livewire::test(CreateRequest::class)
        ->set('destination_country', 'NG')
        ->set('weight_kg', '')
        ->set('payment_amount', '50')
        ->set('payment_currency', 'NGN')
        ->set('payment_method', 'bank_transfer')
        ->call('nextStep')
        ->assertSet('step', 1)
        ->assertHasErrors(['weight_kg']);
});

test('step 1 nextStep fails if payment_amount is missing', function () {
    Livewire::test(CreateRequest::class)
        ->set('destination_country', 'NG')
        ->set('weight_kg', '2.5')
        ->set('payment_amount', '')
        ->set('payment_currency', 'NGN')
        ->set('payment_method', 'bank_transfer')
        ->call('nextStep')
        ->assertSet('step', 1)
        ->assertHasErrors(['payment_amount']);
});

test('step 1 nextStep fails if payment_method is missing', function () {
    Livewire::test(CreateRequest::class)
        ->set('destination_country', 'NG')
        ->set('weight_kg', '2.5')
        ->set('payment_amount', '50')
        ->set('payment_currency', 'NGN')
        ->set('payment_method', '')
        ->call('nextStep')
        ->assertSet('step', 1)
        ->assertHasErrors(['payment_method']);
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

test('create saves the record and dispatches delivery-request-created', function () {
    $description = 'Electronics laptop charger for my nephew in Lagos who urgently needs it for work projects.';

    Livewire::test(CreateRequest::class)
        ->set('step', 2)
        ->set('destination_country', 'NG')
        ->set('weight_kg', '2.5')
        ->set('payment_amount', '50')
        ->set('payment_currency', 'NGN')
        ->set('payment_method', 'bank_transfer')
        ->set('description', $description)
        ->set('expires_in_days', 7)
        ->call('create')
        ->assertDispatched('delivery-request-created');

    expect(DeliveryRequest::count())->toBe(1)
        ->and(DeliveryRequest::first()->user_id)->toBe($this->user->id)
        ->and(DeliveryRequest::first()->destination_country)->toBe('NG');
});

test('calling create twice only creates one record (double-submit guard)', function () {
    $description = 'Electronics laptop charger for my nephew in Lagos who urgently needs it for work projects.';

    $component = Livewire::test(CreateRequest::class)
        ->set('step', 2)
        ->set('destination_country', 'NG')
        ->set('weight_kg', '2.5')
        ->set('payment_amount', '50')
        ->set('payment_currency', 'NGN')
        ->set('payment_method', 'bank_transfer')
        ->set('description', $description)
        ->set('expires_in_days', 7)
        ->call('create');

    $component->set('submitting', true)->call('create');

    expect(DeliveryRequest::count())->toBe(1);
});

test('image is stored to public disk when uploaded', function () {
    Storage::fake('public');

    $description = 'Electronics laptop charger for my nephew in Lagos who urgently needs it for work projects.';
    $image = UploadedFile::fake()->image('item.jpg');

    Livewire::test(CreateRequest::class)
        ->set('step', 2)
        ->set('destination_country', 'NG')
        ->set('weight_kg', '2.5')
        ->set('payment_amount', '50')
        ->set('payment_currency', 'NGN')
        ->set('payment_method', 'bank_transfer')
        ->set('description', $description)
        ->set('item_image', $image)
        ->set('expires_in_days', 7)
        ->call('create');

    $request = DeliveryRequest::first();
    expect($request->item_image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($request->item_image_path);
});

test('Board onRequestCreated listener closes modal', function () {
    Livewire::test(Board::class)
        ->set('showCreateModal', true)
        ->dispatch('delivery-request-created')
        ->assertSet('showCreateModal', false);
});
