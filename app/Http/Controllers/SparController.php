<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SparController extends Controller
{
    private int $limit = 24;

    private int $catalogPageSize = 100;

    private int $catalogMaxPages = 12;

    private array $categories = [
        'chocolate' => 'CHOCOLATE',
        'candy' => 'CANDY',
        'sweets' => 'SWEETS',
        'wafers_biscuits' => 'WAFERS & BISCUITS',
        'chips' => 'CHIPS',
        'nuts_dried' => 'NUTS & DRIED',
        'groceries' => 'GROCERIES',
        'sauces' => 'SAUCES',
        'oil_vinegar' => 'OIL & VINEGAR',
        'pasta' => 'PASTA',
        'cereals' => 'CEREALS',
        'flour_sugar_salt' => 'FLOUR, SUGAR & SALT',
        'canned_goods' => 'CANNED GOODS',
        'meat_fish' => 'MEAT & FISH',
        'fish' => 'FISH',
        'sausages' => 'SAUSAGES',
        'dairy' => 'DAIRY',
        'milk_kefir' => 'MILK & KEFIR',
        'yogurt' => 'YOGURT',
        'cheese' => 'CHEESE',
        'sour_cream' => 'SOUR CREAM',
        'frozen' => 'FROZEN',
        'bakery' => 'BAKERY',
        'vegetables_fruits' => 'VEGETABLES & FRUITS',
        'water' => 'WATER',
        'juice_nectar' => 'JUICE & NECTAR',
        'carbonated' => 'CARBONATED',
        'energy_drinks' => 'ENERGY DRINKS',
        'instant_coffee' => 'INSTANT COFFEE',
        'ground_coffee' => 'GROUND COFFEE',
        'tea' => 'TEA',
        'wine' => 'WINE',
        'beer' => 'BEER',
        'alcohol' => 'ALCOHOL',
        'skin_care' => 'SKIN CARE',
        'hair_care' => 'HAIR CARE',
        'oral_care_pharm' => 'ORAL CARE',
        'hygiene_products' => 'HYGIENE',
        'household' => 'HOUSEHOLD',
        'cleaning' => 'CLEANING',
        'laundry' => 'LAUNDRY',
        'paper_goods' => 'PAPER GOODS',
    ];

    public function index()
    {
        $page = max(1, (int) request('page', 1));
        $category = (string) request('category', '');

        if (! isset($this->categories[$category])) {
            $category = '';
        }

        $search = trim((string) request('search', ''));

        $data = $search !== ''
            ? $this->search($category, $search, $page)
            : $this->browse($category, $page);

        $data['categories'] = $this->categories;
        $data['selectedCategory'] = $category;
        $data['search'] = $search;

        return view('spar.index', $data);
    }

    private function browse(string $category, int $page): array
    {
        $data = $this->cachedPage($category, $page);

        if (! $data['error'] && $data['pages'] > 0 && $page > $data['pages']) {
            return $this->cachedPage($category, $data['pages']);
        }

        return $data;
    }

    private function cachedPage(string $category, int $page): array
    {
        $key = 'spar:browse:' . ($category ?: 'all') . ':' . $page;

        $cached = Cache::get($key);

        if ($cached) {
            return $cached;
        }

        $data = $this->fetchPage($category, $page, $this->limit);

        if (! $data['error']) {
            Cache::put($key, $data, now()->addMinutes(20));
        }

        return $data;
    }

    private function search(string $category, string $term, int $page): array
    {
        $catalog = $this->catalog($category);

        if ($catalog['error']) {
            return ['products' => [], 'error' => true, 'page' => $page, 'pages' => 0, 'total' => 0];
        }

        $filtered = array_values(array_filter($catalog['products'], function ($p) use ($term) {
            return mb_stripos($p['name'], $term) !== false
                || ($p['category'] && mb_stripos($p['category'], $term) !== false);
        }));

        $total = count($filtered);
        $pages = (int) ceil($total / $this->limit);

        if ($pages > 0 && $page > $pages) {
            $page = $pages;
        }

        $offset = max(0, ($page - 1) * $this->limit);
        $products = array_slice($filtered, $offset, $this->limit);

        return ['products' => $products, 'error' => false, 'page' => $page, 'pages' => $pages, 'total' => $total];
    }

    private function catalog(string $category): array
    {
        $key = 'spar:catalog:' . ($category ?: 'all');

        $cached = Cache::get($key);

        if ($cached) {
            return $cached;
        }

        $all = [];
        $error = false;
        $p = 1;

        while ($p <= $this->catalogMaxPages) {
            $res = $this->fetchPage($category, $p, $this->catalogPageSize);

            if ($res['error']) {
                $error = $p === 1;
                break;
            }

            $all = array_merge($all, $res['products']);

            if ($p >= $res['pages']) {
                break;
            }

            $p++;
        }

        $data = ['products' => $all, 'error' => $error && empty($all)];

        if (! $data['error']) {
            Cache::put($key, $data, now()->addMinutes(30));
        }

        return $data;
    }

    private function fetchPage(string $category, int $page, int $limit): array
    {
        $products = [];
        $error = false;
        $total = 0;
        $pages = 0;

        try {
            $params = [
                'store' => 'spar',
                'city' => 'kutaisi',
                'sort' => 'discount',
                'page' => $page,
                'limit' => $limit,
            ];

            if ($category !== '') {
                $params['category'] = $category;
            }

            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (knews)'])
                ->get('https://tarifebi.ge/api/products.php', $params);

            if (! $response->successful()) {
                $error = true;
            } else {
                $payload = $response->json();

                if (! ($payload['success'] ?? false)) {
                    $error = true;
                } else {
                    $total = (int) ($payload['total'] ?? 0);
                    $pages = (int) ($payload['pages'] ?? 0);

                    foreach (($payload['products'] ?? []) as $p) {
                        $products[] = $this->transform($p);
                    }
                }
            }
        } catch (\Throwable $e) {
            $error = true;
        }

        return [
            'products' => $products,
            'error' => $error,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
        ];
    }

    private function transform(array $p): array
    {
        $sale = isset($p['sale_price']) ? (float) $p['sale_price'] : null;
        $original = isset($p['original_price']) ? (float) $p['original_price'] : null;
        $onSale = ($p['is_on_sale'] ?? 0) && $sale !== null && $original !== null && $sale < $original;

        return [
            'name' => $p['name'] ?? '',
            'image' => $p['image_url'] ?? null,
            'price' => $sale ?? $original,
            'original' => $original,
            'discount' => $p['discount_percent'] ?? null,
            'on_sale' => $onSale,
            'unit' => $p['unit'] ?? null,
            'category' => $p['category_name'] ?? null,
        ];
    }
}
