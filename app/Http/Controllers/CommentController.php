<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\News;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, News $news)
    {
        if ($news->status === 'pending' && ! auth()->user()->is_admin) {
            abort(404);
        }

        $validated = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $news->comments()->create([
            'user_id' => auth()->id(),
            'body' => $validated['body'],
        ]);

        return redirect()->route('news.show', $news)->withFragment('comments')->with('success', 'Comment posted.');
    }

    public function destroy(Comment $comment)
    {
        if (auth()->id() !== $comment->user_id && ! auth()->user()->is_admin) {
            abort(403);
        }

        $newsId = $comment->news_id;
        $comment->delete();

        return redirect()->route('news.show', $newsId)->withFragment('comments')->with('success', 'Comment deleted.');
    }
}
