<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Reaction;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    public function store(Request $request, News $news)
    {
        if ($news->status === 'pending' && ! auth()->user()->is_admin) {
            abort(404);
        }

        $validated = $request->validate([
            'type' => ['required', 'in:' . implode(',', array_keys(Reaction::TYPES))],
        ]);

        $existing = $news->reactions()->where('user_id', auth()->id())->first();

        if ($existing && $existing->type === $validated['type']) {
            $existing->delete();
        } elseif ($existing) {
            $existing->update(['type' => $validated['type']]);
        } else {
            $news->reactions()->create([
                'user_id' => auth()->id(),
                'type' => $validated['type'],
            ]);
        }

        return redirect()->route('news.show', $news)->withFragment('reactions');
    }
}
