<?php

use App\Services\ExchangeRateService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(Tests\TestCase::class);

beforeEach(function () {
    Cache::flush();
});

test('getRate returns correct rate on successful API response', function () {
    Http::fake([
        'open.er-api.com/*' => Http::response([
            'result' => 'success',
            'base_code' => 'NGN',
            'rates' => ['RWF' => 0.63, 'USD' => 0.00064],
        ], 200),
    ]);

    $service = new ExchangeRateService;
    $rate = $service->getRate('NGN', 'RWF');

    expect($rate)->toBe(0.63);
});

test('getRate returns null on API failure', function () {
    Http::fake([
        'open.er-api.com/*' => Http::response([], 500),
    ]);

    $service = new ExchangeRateService;
    $rate = $service->getRate('NGN', 'RWF');

    expect($rate)->toBeNull();
});

test('getRate returns null when API result is not success', function () {
    Http::fake([
        'open.er-api.com/*' => Http::response([
            'result' => 'error',
            'error-type' => 'unknown-code',
        ], 200),
    ]);

    $service = new ExchangeRateService;
    $rate = $service->getRate('INVALID', 'RWF');

    expect($rate)->toBeNull();
});

test('second call uses cache and does not make another HTTP request', function () {
    Http::fake([
        'open.er-api.com/*' => Http::response([
            'result' => 'success',
            'base_code' => 'NGN',
            'rates' => ['RWF' => 0.63],
        ], 200),
    ]);

    $service = new ExchangeRateService;

    $rate1 = $service->getRate('NGN', 'RWF');
    $rate2 = $service->getRate('NGN', 'RWF');

    expect($rate1)->toBe(0.63)
        ->and($rate2)->toBe(0.63);

    Http::assertSentCount(1);
});

test('getRates returns full rates array on success', function () {
    Http::fake([
        'open.er-api.com/*' => Http::response([
            'result' => 'success',
            'base_code' => 'NGN',
            'rates' => ['RWF' => 0.63, 'USD' => 0.00064, 'KES' => 0.082],
        ], 200),
    ]);

    $service = new ExchangeRateService;
    $rates = $service->getRates('NGN');

    expect($rates)->toBeArray()
        ->and($rates['RWF'])->toBe(0.63)
        ->and($rates['USD'])->toBe(0.00064);
});

test('getRates returns null on connection failure', function () {
    Http::fake([
        'open.er-api.com/*' => Http::response(null, 503),
    ]);

    $service = new ExchangeRateService;
    $rates = $service->getRates('NGN');

    expect($rates)->toBeNull();
});
