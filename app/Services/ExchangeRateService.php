<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    private const API_BASE = 'https://open.er-api.com/v6/latest';

    private const CACHE_TTL = 3600;

    /**
     * Get the exchange rate from one currency to another.
     */
    public function getRate(string $from, string $to): ?float
    {
        $rates = $this->getRates($from);

        if ($rates === null) {
            return null;
        }

        return $rates[$to] ?? null;
    }

    /**
     * Get all rates for a given base currency.
     *
     * @return array<string, float>|null
     */
    public function getRates(string $base): ?array
    {
        $cacheKey = "exchange_rates_{$base}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($base): ?array {
            try {
                $response = Http::timeout(10)->get(self::API_BASE."/{$base}");

                if (! $response->successful()) {
                    return null;
                }

                $data = $response->json();

                if (($data['result'] ?? null) !== 'success') {
                    return null;
                }

                return $data['rates'] ?? null;
            } catch (\Exception $e) {
                Log::warning('ExchangeRateService failed', ['base' => $base, 'error' => $e->getMessage()]);

                return null;
            }
        });
    }
}
