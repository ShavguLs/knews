@extends('layouts.app')

@section('title', $user->name . ' | KNEWS')

@section('content')
    <div class="profile">
        <div class="profile__header">
            <div class="profile__avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <div class="profile__meta">
                <h1 class="profile__name">
                    {{ strtoupper($user->name) }}
                    @if($user->is_admin)<span class="profile__badge">ADMIN</span>@endif
                </h1>
                <span class="profile__since">MEMBER SINCE {{ strtoupper($user->created_at->format('M Y')) }}</span>
                <div class="profile__stats">
                    <span class="profile__stat"><strong>{{ $commentCount }}</strong> COMMENTS</span>
                    <span class="profile__stat"><strong>{{ $reactionCount }}</strong> REACTIONS</span>
                </div>
            </div>
        </div>

        <div class="section-header">
            <h3 class="section-title">COMMENTS</h3>
            <div class="section-line"></div>
        </div>

        @forelse($comments as $comment)
            <article class="comment">
                <div class="comment__head">
                    <a class="comment__author" href="{{ route('news.show', $comment->news) }}">{{ strtoupper($comment->news->title) }}</a>
                    <span class="comment__date">{{ $comment->created_at->format('M j, Y · H:i') }}</span>
                </div>
                <p class="comment__body">{{ $comment->body }}</p>
            </article>
        @empty
            <p class="comments__empty">No comments yet.</p>
        @endforelse
    </div>
@endsection
