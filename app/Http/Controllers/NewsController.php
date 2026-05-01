<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index()
    {
        $newsList = News::orderBy('created_at', 'desc')->get();

        if (request()->routeIs('admin.*')) {
            return view('admin.news.index', compact('newsList'));
        }

        return view('news.index', compact('newsList'));
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
        ]);

        News::create($validated);

        return redirect()->route('admin.news.index')->with('success', 'News created successfully.');
    }

    public function show(News $news)
    {
        return view('news.show', compact('news'));
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
        ]);

        $news->update($validated);

        return redirect()->route('admin.news.index')->with('success', 'News updated successfully.');
    }

    public function destroy(News $news)
    {
        $news->delete();

        return redirect()->route('admin.news.index')->with('success', 'News deleted successfully.');
    }
}