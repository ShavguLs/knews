<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index()
    {
        if (request()->routeIs('admin.*')) {
            $newsList = News::orderBy('created_at', 'desc')->get();
            return view('admin.news.index', compact('newsList'));
        }

        $search = request('search', '');

        $hero = News::where('status', 'done')
            ->where('is_hero', true)
            ->first();

        if (!$hero) {
            $hero = News::where('status', 'done')
                ->orderBy('created_at', 'desc')
                ->first();
        }

        $dispatches = News::where('status', 'done')
            ->when($hero, fn($q) => $q->where('id', '!=', $hero->id))
            ->orderBy('created_at', 'desc');

        if ($search) {
            $dispatches->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%");
            });
        }

        $dispatches = $dispatches->get();

        $weather = app(WeatherController::class)->data();
        $crypto = app(CryptoController::class)->data();
        $currency = app(CurrencyController::class)->data();
        $air = app(AirQualityController::class)->data();

        $ticker = [
            'weather' => ! $weather['error'] ? ($weather['spotlight'] ?? null) : null,
            'air' => ! $air['error'] ? ($air['spotlight'] ?? null) : null,
            'btc' => ! $crypto['error'] ? ($crypto['spotlight'] ?? null) : null,
            'eth' => ! $crypto['error'] ? ($crypto['coins'][0] ?? null) : null,
            'usd' => ! $currency['error'] ? ($currency['spotlight'] ?? null) : null,
            'eur' => ! $currency['error'] ? ($currency['currencies'][0] ?? null) : null,
            'headline' => $hero,
        ];

        return view('news.index', compact('hero', 'dispatches', 'search', 'ticker'));
    }

    public function create()
    {
        return view('news.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'category' => 'required|max:100',
            'author' => 'required|max:100',
            'body' => 'required',
            'image_url' => 'nullable|url|max:255',
            'published_at' => 'nullable|date',
            'status' => 'required|in:pending,done',
            'is_hero' => 'nullable|boolean',
        ]);

        $validated['is_hero'] = $request->boolean('is_hero');

        if ($validated['is_hero']) {
            News::where('is_hero', true)->update(['is_hero' => false]);
        }

        News::create($validated);

        return redirect()->route('admin.news.index')->with('success', 'News created successfully.');
    }

    public function show(News $news)
    {
        if ($news->status === 'pending' && (!auth()->check() || !auth()->user()->is_admin)) {
            abort(404);
        }

        $news->load(['comments' => fn($q) => $q->latest()->with('user')]);

        $reactionCounts = $news->reactions()
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $userReaction = auth()->check()
            ? $news->reactions()->where('user_id', auth()->id())->value('type')
            : null;

        $isBookmarked = auth()->check()
            && $news->bookmarks()->where('user_id', auth()->id())->exists();

        return view('news.show', compact('news', 'reactionCounts', 'userReaction', 'isBookmarked'));
    }

    public function edit(News $news)
    {
        return view('news.edit', compact('news'));
    }

    public function update(Request $request, News $news)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'category' => 'required|max:100',
            'author' => 'required|max:100',
            'body' => 'required',
            'image_url' => 'nullable|url|max:255',
            'published_at' => 'nullable|date',
            'status' => 'required|in:pending,done',
            'is_hero' => 'nullable|boolean',
        ]);

        $validated['is_hero'] = $request->boolean('is_hero');

        if ($validated['is_hero']) {
            News::where('is_hero', true)->where('id', '!=', $news->id)->update(['is_hero' => false]);
        }

        $news->update($validated);

        return redirect()->route('admin.news.index')->with('success', 'News updated successfully.');
    }

    public function destroy(News $news)
    {
        $news->delete();

        return redirect()->route('admin.news.index')->with('success', 'News deleted successfully.');
    }
}