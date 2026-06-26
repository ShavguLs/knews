<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $validated = $this->validateNews($request);

        $validated['is_hero'] = $request->boolean('is_hero');
        unset($validated['image_file']);

        if ($request->hasFile('image_file')) {
            $validated['image_path'] = $request->file('image_file')->store('news-images', 'public');
        }

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
        $validated = $this->validateNews($request);

        $validated['is_hero'] = $request->boolean('is_hero');
        unset($validated['image_file']);

        if ($request->hasFile('image_file')) {
            if ($news->image_path) {
                Storage::disk('public')->delete($news->image_path);
            }

            $validated['image_path'] = $request->file('image_file')->store('news-images', 'public');
        }

        if ($validated['is_hero']) {
            News::where('is_hero', true)->where('id', '!=', $news->id)->update(['is_hero' => false]);
        }

        $news->update($validated);

        return redirect()->route('admin.news.index')->with('success', 'News updated successfully.');
    }

    public function destroy(News $news)
    {
        if ($news->image_path) {
            Storage::disk('public')->delete($news->image_path);
        }

        $news->delete();

        return redirect()->route('admin.news.index')->with('success', 'News deleted successfully.');
    }

    private function validateNews(Request $request): array
    {
        return $request->validate([
            'title' => 'required|max:255',
            'category' => 'required|max:100',
            'author' => 'required|max:100',
            'body' => 'required',
            'image_url' => 'nullable|url|max:255',
            'image_file' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'published_at' => 'nullable|date',
            'status' => 'required|in:pending,done',
            'is_hero' => 'nullable|boolean',
        ]);
    }
}
