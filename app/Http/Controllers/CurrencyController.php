<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CurrencyController extends Controller
{
    private array $codes = [
        'USD',
        'EUR',
        'GBP',
        'TRY',
        'RUB',
        'AZN',
        'AMD',
        'UAH',
        'CNY',
        'JPY',
        'CHF',
        'CAD',
    ];

    public function index()
    {
        return view('currency.index', $this->data());
    }

    public function data(): array
    {
        $cached = Cache::get('currency.gel');

        if ($cached) {
            return $cached;
        }

        $data = $this->fetch();

        if (! $data['error']) {
            Cache::put('currency.gel', $data, now()->addMinutes(30));
        }

        return $data;
    }

    private function fetch(): array
    {
        $spotlight = null;
        $currencies = [];
        $error = false;
        $updatedAt = null;

        try {
            $response = Http::timeout(8)->get('https://nbg.gov.ge/gw/api/ct/monetarypolicy/currencies/en/json/');

            if (! $response->successful()) {
                $error = true;
            } else {
                $payload = $response->json();
                $list = $payload[0]['currencies'] ?? null;

                if (! is_array($list)) {
                    $error = true;
                } else {
                    $byCode = [];
                    foreach ($list as $c) {
                        if (isset($c['code'])) {
                            $byCode[$c['code']] = $c;
                        }
                    }

                    foreach ($this->codes as $code) {
                        if (! isset($byCode[$code])) {
                            continue;
                        }

                        $entry = $this->transform($byCode[$code]);

                        if ($code === 'USD') {
                            $spotlight = $entry;
                            $updatedAt = $entry['updated_at'];
                        } else {
                            $currencies[] = $entry;
                        }
                    }

                    if (! $spotlight && empty($currencies)) {
                        $error = true;
                    }
                }
            }
        } catch (\Throwable $e) {
            $error = true;
        }

        return compact('spotlight', 'currencies', 'error', 'updatedAt');
    }

    private function transform(array $c): array
    {
        $quantity = $c['quantity'] ?? 1;
        $rate = $c['rate'] ?? 0;
        $diff = $c['diff'] ?? 0;
        $perUnit = $quantity > 0 ? $rate / $quantity : $rate;
        $prev = $rate - $diff;
        $changePct = $prev != 0 ? ($diff / $prev) * 100 : null;

        return [
            'code' => $c['code'] ?? '',
            'name' => $c['name'] ?? '',
            'per_unit' => $perUnit,
            'inverse' => $perUnit > 0 ? 1 / $perUnit : null,
            'change' => $changePct,
            'updated_at' => isset($c['date']) ? Carbon::parse($c['date'])->format('M j, H:i') : null,
        ];
    }
}
