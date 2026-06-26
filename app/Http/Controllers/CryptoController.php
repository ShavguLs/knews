<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CryptoController extends Controller
{
    private array $ids = [
        'bitcoin',
        'ethereum',
        'solana',
        'ripple',
        'dogecoin',
        'cardano',
        'tron',
        'chainlink',
        'avalanche-2',
        'litecoin',
        'polkadot',
        'stellar',
    ];

    public function index()
    {
        $cached = Cache::get('crypto.markets');

        if ($cached) {
            return view('crypto.index', $cached);
        }

        $data = $this->fetch();

        if (! $data['error']) {
            Cache::put('crypto.markets', $data, now()->addMinutes(5));
        }

        return view('crypto.index', $data);
    }

    private function fetch(): array
    {
        $spotlight = null;
        $coins = [];
        $error = false;
        $updatedAt = null;

        try {
            $response = Http::timeout(8)->get('https://api.coingecko.com/api/v3/coins/markets', [
                'vs_currency' => 'usd',
                'ids' => implode(',', $this->ids),
                'order' => 'market_cap_desc',
                'per_page' => count($this->ids),
                'page' => 1,
                'sparkline' => 'false',
                'price_change_percentage' => '24h',
            ]);

            if (! $response->successful()) {
                $error = true;
            } else {
                $payload = $response->json();

                if (! is_array($payload) || empty($payload)) {
                    $error = true;
                } else {
                    foreach ($payload as $index => $coin) {
                        $entry = $this->transform($coin);

                        if ($index === 0) {
                            $spotlight = $entry;
                            $updatedAt = $entry['updated_at'];
                        } else {
                            $coins[] = $entry;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            $error = true;
        }

        return compact('spotlight', 'coins', 'error', 'updatedAt');
    }

    private function transform(array $coin): array
    {
        return [
            'name' => $coin['name'] ?? 'Unknown',
            'symbol' => strtoupper($coin['symbol'] ?? ''),
            'image' => $coin['image'] ?? null,
            'rank' => $coin['market_cap_rank'] ?? null,
            'price' => $coin['current_price'] ?? null,
            'change' => $coin['price_change_percentage_24h'] ?? null,
            'high' => $coin['high_24h'] ?? null,
            'low' => $coin['low_24h'] ?? null,
            'market_cap' => $coin['market_cap'] ?? null,
            'volume' => $coin['total_volume'] ?? null,
            'updated_at' => isset($coin['last_updated']) ? Carbon::parse($coin['last_updated'])->format('M j, H:i') : null,
        ];
    }
}
