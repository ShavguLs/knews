@extends('layouts.app')

@section('title', 'KNEWS | THE FINAL WORD IN JOURNALISM')

@php
$categoryClassMap = [
    'tech' => 'category--tech',
    'culture' => 'category--culture',
    'opinion' => 'category--opinion',
    'sports' => 'category--sports',
    'politics' => 'category--politics',
];
$fallbackImage = 'https://lh3.googleusercontent.com/aida-public/AB6AXuB3LYG0wfKd8oj34vEa4xqPV_t8xl4TRrCzrRBCSLqvxPjeUto3ow0QU_JgcgsCh0VEcD9F-_v0jUwKkBgfXfgGibrQueI3wVUIgsKImEfIEll_ME2GrjDmzGBdgAAxIZkQzamnvlhuU3HOd1AI166mKRxsccaepolfA1jhU_MWubjFkuklFl8NEewOr2gicOyHkB4RN6ztOO5ITdFNLOOXH0a3yNmyVBRUX7kf2RDzm3mb9V__Yo7niRL_p1SA_8_2qUlFCHEw-zR';
@endphp

@section('content')
    @if($newsList->isEmpty())
        <div class="empty-state">
            <h2 class="empty-state__title">NO DISPATCHES YET</h2>
            <p class="empty-state__text">The pressroom is quiet. Check back soon for the latest dispatches.</p>
        </div>
    @else
        @php
            $hero = $newsList->first();
            $rest = $newsList->skip(1);
            $heroCategory = strtolower($hero->category ?? '');
            $heroClass = $categoryClassMap[$heroCategory] ?? 'category--tech';
            $heroImage = $hero->image_url ?: $fallbackImage;
            $heroDate = $hero->published_at ? $hero->published_at->format('F j, Y') : 'UNPUBLISHED';
        @endphp

        <section class="hero">
            <div class="hero__content">
                <span class="hero__kicker {{ $heroClass }}">{{ strtoupper($hero->category ?? 'NEWS') }}</span>
                <h2 class="hero__title">{{ strtoupper($hero->title) }}</h2>
                <p class="hero__summary">{{ \Illuminate\Support\Str::limit($hero->body, 200) }}</p>
                <div class="hero__meta">
                    <a class="hero__cta" href="{{ route('news.show', $hero) }}">READ MORE</a>
                    <div class="hero__byline">
                        <span class="hero__author">BY {{ strtoupper($hero->author ?? 'UNKNOWN') }}</span>
                        <span class="hero__date">{{ $heroDate }}</span>
                    </div>
                </div>
            </div>
            <div class="hero__media">
                <div class="hero__image-frame">
                    <img alt="{{ $hero->title }}" class="hero__image" src="{{ $heroImage }}">
                </div>
            </div>
        </section>

        @if($rest->count() > 0)
            <div class="section-header">
                <h3 class="section-title">LATEST DISPATCHES</h3>
                <div class="section-line"></div>
                <span class="section-issue">{{ $rest->count() }} NEWS</span>
            </div>

            <section class="news-grid">
                @foreach($rest as $news)
                    @php
                        $cat = strtolower($news->category ?? '');
                        $catClass = $categoryClassMap[$cat] ?? 'category--tech';
                        $newsDate = $news->published_at ? $news->published_at->format('M j, Y') : 'UNPUBLISHED';
                    @endphp
                    <article class="news-card">
                        <div class="news-card__header category {{ $catClass }}">
                            <span class="category__label">{{ strtoupper($news->category ?? 'NEWS') }}</span>
                        </div>
                        <div class="news-card__body">
                            <h4 class="news-card__title">{{ $news->title }}</h4>
                            <p class="news-card__summary">{{ \Illuminate\Support\Str::limit($news->body, 150) }}</p>
                        </div>
                        <a href="{{ route('news.show', $news) }}" class="news-card__footer">
                            <div class="news-card__meta">
                                <span class="news-card__author">{{ strtoupper($news->author ?? 'UNKNOWN') }}</span>
                                <span class="news-card__time">{{ $newsDate }}</span>
                            </div>
                            <span class="material-symbols-outlined news-card__icon" data-icon="arrow_forward">arrow_forward</span>
                        </a>
                    </article>
                @endforeach
            </section>
        @endif
    @endif
@endsection