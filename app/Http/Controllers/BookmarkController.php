<?php

namespace App\Http\Controllers;

use App\Models\News;

class BookmarkController extends Controller
{
    public function index()
    {
        $bookmarks = auth()->user()->bookmarkedNews()
            ->where('status', 'done')
            ->orderByDesc('bookmarks.created_at')
            ->get();

        return view('bookmarks.index', compact('bookmarks'));
    }

    public function toggle(News $news)
    {
        if ($news->status === 'pending' && ! auth()->user()->is_admin) {
            abort(404);
        }

        $existing = $news->bookmarks()->where('user_id', auth()->id())->first();

        if ($existing) {
            $existing->delete();

            return redirect()->back()->with('success', 'Removed from your saved articles.');
        }

        $news->bookmarks()->create(['user_id' => auth()->id()]);

        return redirect()->back()->with('success', 'Saved for later.');
    }
}
