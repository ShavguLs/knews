<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\JsonResponse;

class NewsApiController extends Controller
{
    public function index(): JsonResponse
    {
        $articles = News::where('status', 'done')
            ->latest('published_at')
            ->latest('created_at')
            ->get()
            ->map(fn (News $news) => [
                'id' => $news->id,
                'title' => $news->title,
                'category' => $news->category,
                'author' => $news->author,
                'body' => $news->body,
                'image_url' => $news->imageSource(),
                'published_at' => $news->published_at?->toIso8601String(),
                'url' => route('news.show', $news),
            ]);

        return response()->json([
            'data' => $articles,
        ]);
    }
}
