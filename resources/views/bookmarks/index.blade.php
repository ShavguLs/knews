@extends('layouts.app')

@section('title', 'Saved Articles | KNEWS')

@php
$categoryClassMap = [
    'tech' => 'category--tech',
    'culture' => 'category--culture',
    'opinion' => 'category--opinion',
    'sports' => 'category--sports',
    'politics' => 'category--politics',
];
@endphp

@section('content')
    <div class="section-header">
        <h3 class="section-title">SAVED ARTICLES</h3>
        <div class="section-line"></div>
    </div>

    @if($bookmarks->isEmpty())
        <div class="empty-state">
            <h2 class="empty-state__title">NOTHING SAVED YET</h2>
            <p class="empty-state__text">Hit SAVE on any dispatch to keep it here for later.</p>
        </div>
    @else
        <section class="news-grid">
            @foreach($bookmarks as $news)
                @php
                    $cat = strtolower($news->category ?? '');
                    $catClass = $categoryClassMap[$cat] ?? 'category--tech';
                @endphp
                <article class="news-card">
                    <div class="news-card__header category {{ $catClass }}">
                        <span class="category__label">{{ strtoupper($news->category ?? 'NEWS') }}</span>
                    </div>
                    <div class="news-card__body">
                        <h4 class="news-card__title">{{ $news->title }}</h4>
                        <p class="news-card__summary">{{ \Illuminate\Support\Str::limit($news->body, 150) }}</p>
                    </div>
                    <div class="news-card__footer">
                        <a href="{{ route('news.show', $news) }}" class="btn-brutal btn-brutal--stone">READ</a>
                        <form action="{{ route('bookmarks.toggle', $news) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-brutal btn-brutal--red">REMOVE</button>
                        </form>
                    </div>
                </article>
            @endforeach
        </section>
    @endif
@endsection
