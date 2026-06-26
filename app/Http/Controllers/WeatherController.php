<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
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
        return view('weather.index', $this->data());
    }

    public function data(): array
    {
        $cached = Cache::get('weather.georgia');

        if ($cached) {
            return $cached;
        }

        $data = $this->fetch();

        if (! $data['error']) {
            Cache::put('weather.georgia', $data, now()->addMinutes(20));
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
            $response = Http::timeout(8)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $latitudes,
                'longitude' => $longitudes,
                'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,weather_code,wind_speed_10m',
                'daily' => 'weather_code,temperature_2m_max,temperature_2m_min',
                'timezone' => 'auto',
                'forecast_days' => 5,
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

                    $weather = $this->transform($city, $results[$index]);

                    if ($city['spotlight']) {
                        $spotlight = $weather;
                        $updatedAt = $weather['updated_at'];
                    } else {
                        $cities[] = $weather;
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
        $daily = $data['daily'] ?? [];
        $condition = $this->describe($current['weather_code'] ?? null);

        $forecast = [];
        foreach (($daily['time'] ?? []) as $i => $date) {
            $forecast[] = [
                'label' => strtoupper(Carbon::parse($date)->format('D')),
                'max' => isset($daily['temperature_2m_max'][$i]) ? round($daily['temperature_2m_max'][$i]) : null,
                'min' => isset($daily['temperature_2m_min'][$i]) ? round($daily['temperature_2m_min'][$i]) : null,
                'icon' => $this->describe($daily['weather_code'][$i] ?? null)['icon'],
            ];
        }

        return [
            'name' => $city['name'],
            'region' => strtoupper($city['region']),
            'temp' => isset($current['temperature_2m']) ? round($current['temperature_2m']) : null,
            'feels' => isset($current['apparent_temperature']) ? round($current['apparent_temperature']) : null,
            'humidity' => $current['relative_humidity_2m'] ?? null,
            'wind' => isset($current['wind_speed_10m']) ? round($current['wind_speed_10m']) : null,
            'label' => $condition['label'],
            'icon' => $condition['icon'],
            'updated_at' => isset($current['time']) ? Carbon::parse($current['time'])->format('M j, H:i') : null,
            'forecast' => $forecast,
        ];
    }

    private function describe(?int $code): array
    {
        $map = [
            0 => ['Clear sky', 'sunny'],
            1 => ['Mainly clear', 'sunny'],
            2 => ['Partly cloudy', 'partly_cloudy_day'],
            3 => ['Overcast', 'cloud'],
            45 => ['Fog', 'foggy'],
            48 => ['Rime fog', 'foggy'],
            51 => ['Light drizzle', 'rainy'],
            53 => ['Drizzle', 'rainy'],
            55 => ['Dense drizzle', 'rainy'],
            56 => ['Freezing drizzle', 'rainy'],
            57 => ['Freezing drizzle', 'rainy'],
            61 => ['Light rain', 'rainy'],
            63 => ['Rain', 'rainy'],
            65 => ['Heavy rain', 'rainy'],
            66 => ['Freezing rain', 'rainy'],
            67 => ['Freezing rain', 'rainy'],
            71 => ['Light snow', 'weather_snowy'],
            73 => ['Snow', 'weather_snowy'],
            75 => ['Heavy snow', 'weather_snowy'],
            77 => ['Snow grains', 'weather_snowy'],
            80 => ['Light showers', 'rainy'],
            81 => ['Showers', 'rainy'],
            82 => ['Violent showers', 'rainy'],
            85 => ['Snow showers', 'weather_snowy'],
            86 => ['Snow showers', 'weather_snowy'],
            95 => ['Thunderstorm', 'thunderstorm'],
            96 => ['Thunderstorm', 'thunderstorm'],
            99 => ['Thunderstorm', 'thunderstorm'],
        ];

        $entry = $map[$code] ?? ['Unknown', 'thermostat'];

        return ['label' => strtoupper($entry[0]), 'icon' => $entry[1]];
    }
}
