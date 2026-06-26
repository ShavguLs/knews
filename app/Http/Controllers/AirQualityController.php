<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AirQualityController extends Controller
{
    private array $cities = [
        ['name' => 'Kutaisi', 'region' => 'Imereti', 'lat' => 42.2679, 'lon' => 42.7180, 'spotlight' => true],
        ['name' => 'Tbilisi', 'region' => 'Capital', 'lat' => 41.7151, 'lon' => 44.8271, 'spotlight' => false],
        ['name' => 'Batumi', 'region' => 'Adjara', 'lat' => 41.6460, 'lon' => 41.6406, 'spotlight' => false],
        ['name' => 'Rustavi', 'region' => 'Kvemo Kartli', 'lat' => 41.5495, 'lon' => 45.0028, 'spotlight' => false],
        ['name' => 'Zugdidi', 'region' => 'Samegrelo', 'lat' => 42.5088, 'lon' => 41.8700, 'spotlight' => false],
        ['name' => 'Gori', 'region' => 'Shida Kartli', 'lat' => 41.9847, 'lon' => 44.1086, 'spotlight' => false],
        ['name' => 'Telavi', 'region' => 'Kakheti', 'lat' => 41.9197, 'lon' => 45.4731, 'spotlight' => false],
        ['name' => 'Poti', 'region' => 'Samegrelo', 'lat' => 42.1508, 'lon' => 41.6716, 'spotlight' => false],
    ];

    public function index()
    {
        return view('air.index', $this->data());
    }

    public function data(): array
    {
        $cached = Cache::get('air.georgia');

        if ($cached) {
            return $cached;
        }

        $data = $this->fetch();

        if (! $data['error']) {
            Cache::put('air.georgia', $data, now()->addMinutes(20));
        }

        return $data;
    }

    private function fetch(): array
    {
        $latitudes = implode(',', array_column($this->cities, 'lat'));
        $longitudes = implode(',', array_column($this->cities, 'lon'));

        $spotlight = null;
        $cities = [];
        $error = false;
        $updatedAt = null;

        try {
            $response = Http::timeout(8)->get('https://air-quality-api.open-meteo.com/v1/air-quality', [
                'latitude' => $latitudes,
                'longitude' => $longitudes,
                'current' => 'us_aqi,pm2_5,pm10,ozone,nitrogen_dioxide,carbon_monoxide',
                'timezone' => 'auto',
            ]);

            if (! $response->successful()) {
                $error = true;
            } else {
                $payload = $response->json();
                $results = array_key_exists(0, $payload) ? $payload : [$payload];

                foreach ($this->cities as $index => $city) {
                    if (! isset($results[$index])) {
                        continue;
                    }

                    $entry = $this->transform($city, $results[$index]);

                    if ($city['spotlight']) {
                        $spotlight = $entry;
                        $updatedAt = $entry['updated_at'];
                    } else {
                        $cities[] = $entry;
                    }
                }

                if (! $spotlight && empty($cities)) {
                    $error = true;
                }
            }
        } catch (\Throwable $e) {
            $error = true;
        }

        return compact('spotlight', 'cities', 'error', 'updatedAt');
    }

    private function transform(array $city, array $data): array
    {
        $current = $data['current'] ?? [];
        $aqi = $current['us_aqi'] ?? null;
        $cat = $this->describe($aqi);

        return [
            'name' => $city['name'],
            'region' => strtoupper($city['region']),
            'aqi' => $aqi !== null ? round($aqi) : null,
            'label' => $cat['label'],
            'class' => $cat['class'],
            'pm25' => isset($current['pm2_5']) ? round($current['pm2_5'], 1) : null,
            'pm10' => isset($current['pm10']) ? round($current['pm10'], 1) : null,
            'ozone' => isset($current['ozone']) ? round($current['ozone']) : null,
            'no2' => isset($current['nitrogen_dioxide']) ? round($current['nitrogen_dioxide'], 1) : null,
            'updated_at' => isset($current['time']) ? Carbon::parse($current['time'])->format('M j, H:i') : null,
        ];
    }

    private function describe(?float $aqi): array
    {
        if ($aqi === null) {
            return ['label' => 'UNKNOWN', 'class' => 'aqi--unknown'];
        }
        if ($aqi <= 50) {
            return ['label' => 'GOOD', 'class' => 'aqi--good'];
        }
        if ($aqi <= 100) {
            return ['label' => 'MODERATE', 'class' => 'aqi--moderate'];
        }
        if ($aqi <= 150) {
            return ['label' => 'SENSITIVE', 'class' => 'aqi--sensitive'];
        }
        if ($aqi <= 200) {
            return ['label' => 'UNHEALTHY', 'class' => 'aqi--unhealthy'];
        }
        if ($aqi <= 300) {
            return ['label' => 'VERY UNHEALTHY', 'class' => 'aqi--very'];
        }

        return ['label' => 'HAZARDOUS', 'class' => 'aqi--hazard'];
    }
}
