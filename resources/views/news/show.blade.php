@extends('layouts.app')

@section('title', $news->title . ' | KNEWS')

@php
    $fallbackImage = 'https://lh3.googleusercontent.com/aida-public/AB6AXuB3LYG0wfKd8oj34vEa4xqPV_t8xl4TRrCzrRBCSLqvxPjeUto3ow0QU_JgcgsCh0VEcD9F-_v0jUwKkBgfXfgGibrQueI3wVUIgsKImEfIEll_ME2GrjDmzGBdgAAxIZkQzamnvlhuU3HOd1AI166mKRxsccaepolfA1jhU_MWubjFkuklFl8NEewOr2gicOyHkB4RN6ztOO5ITdFNLOOXH0a3yNmyVBRUX7kf2RDzm3mb9V__Yo7niRL_p1SA_8_2qUlFCHEw-zR';
    $articleImage = $news->image_url ?: $fallbackImage;
    $articleDate = $news->published_at ? $news->published_at->format('F j, Y') : 'UNPUBLISHED';
@endphp

@section('content')
    <div class="article-detail">
        <span class="article-detail__kicker">{{ strtoupper($news->category ?? 'NEWS') }}</span>
        <h1 class="article-detail__title">{{ $news->title }}</h1>
        <div class="article-detail__byline">
            <span class="article-detail__author">BY {{ strtoupper($news->author ?? 'UNKNOWN') }}</span>
            <span class="article-detail__date">{{ $articleDate }}</span>
        </div>

        <div class="article-detail__image-frame">
            <img alt="{{ $news->title }}" class="article-detail__image" src="{{ $articleImage }}">
        </div>

        <div class="article-detail__body">{{ $news->body }}</div>

        <section id="reactions" class="reactions">
            <h3 class="reactions__title">REACTIONS</h3>
            <div class="reactions__bar">
                @foreach(\App\Models\Reaction::TYPES as $key => $emoji)
                    @php $count = $reactionCounts[$key] ?? 0; @endphp
                    @auth
                        <form action="{{ route('reactions.store', $news) }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="{{ $key }}">
                            <button type="submit" class="reaction {{ $userReaction === $key ? 'reaction--active' : '' }}">
                                <span class="reaction__emoji">{{ $emoji }}</span>
                                <span class="reaction__count">{{ $count }}</span>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="reaction">
                            <span class="reaction__emoji">{{ $emoji }}</span>
                            <span class="reaction__count">{{ $count }}</span>
                        </a>
                    @endauth
                @endforeach
            </div>
        </section>

        <section id="comments" class="comments">
            <h3 class="comments__title">COMMENTS ({{ $news->comments->count() }})</h3>

            @auth
                <form action="{{ route('comments.store', $news) }}" method="POST" class="comment-form">
                    @csrf
                    <textarea class="form-panel__textarea" name="body" rows="4" placeholder="WRITE A COMMENT">{{ old('body') }}</textarea>
                    @error('body')<div class="form-panel__error">{{ $message }}</div>@enderror
                    <div class="comment-form__actions">
                        <button type="submit" class="subscribe-button">POST COMMENT</button>
                    </div>
                </form>
            @else
                <div class="comments__prompt">
                    <span>Join the conversation.</span>
                    <a href="{{ route('login') }}" class="btn-brutal">LOG IN</a>
                    <a href="{{ route('register') }}" class="btn-brutal btn-brutal--red">REGISTER</a>
                </div>
            @endauth

            @forelse($news->comments as $comment)
                <article class="comment">
                    <div class="comment__head">
                        <span class="comment__author">{{ strtoupper($comment->user->name ?? 'UNKNOWN') }}</span>
                        <span class="comment__date">{{ $comment->created_at->format('M j, Y · H:i') }}</span>
                    </div>
                    <p class="comment__body">{{ $comment->body }}</p>
                    @auth
                        @if(auth()->id() === $comment->user_id || auth()->user()->is_admin)
                            <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="comment__delete">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="comment__delete-btn">DELETE</button>
                            </form>
                        @endif
                    @endauth
                </article>
            @empty
                <p class="comments__empty">No comments yet. Be the first to weigh in.</p>
            @endforelse
        </section>

        <a class="article-detail__back" href="{{ route('news.index') }}">BACK TO DISPATCHES</a>
    </div>
@endsection
