<?php

namespace App\Http\Controllers;

use App\Models\User;

class ProfileController extends Controller
{
    public function show(User $user)
    {
        $publishedComments = $user->comments()
            ->whereHas('news', fn($q) => $q->where('status', 'done'));

        $comments = (clone $publishedComments)
            ->with('news')
            ->latest()
            ->take(20)
            ->get();

        $commentCount = $publishedComments->count();
        $reactionCount = $user->reactions()->count();

        return view('profile.show', compact('user', 'comments', 'commentCount', 'reactionCount'));
    }
}
