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
    @if($ticker['weather'] || $ticker['air'] || $ticker['btc'] || $ticker['eth'] || $ticker['usd'] || $ticker['eur'] || $ticker['headline'])
        <div class="ticker">
            @if($ticker['weather'])
                <a href="{{ route('weather.index') }}" class="ticker__item">
                    <span class="ticker__label">KUTAISI</span>
                    <span class="material-symbols-outlined ticker__icon">{{ $ticker['weather']['icon'] }}</span>
                    <span class="ticker__value">{{ $ticker['weather']['temp'] !== null ? $ticker['weather']['temp'] . '°' : '—' }}</span>
                </a>
            @endif
            @if($ticker['air'])
                <a href="{{ route('air.index') }}" class="ticker__item">
                    <span class="ticker__label">AIR</span>
                    <span class="ticker__value">{{ $ticker['air']['aqi'] !== null ? $ticker['air']['aqi'] : '—' }}</span>
                    <span class="aqi-badge {{ $ticker['air']['class'] }}">{{ $ticker['air']['label'] }}</span>
                </a>
            @endif
            @foreach(['btc', 'eth'] as $coin)
                @if($ticker[$coin])
                    @php $up = ($ticker[$coin]['change'] ?? 0) >= 0; @endphp
                    <a href="{{ route('crypto.index') }}" class="ticker__item">
                        <span class="ticker__label">{{ $ticker[$coin]['symbol'] }}</span>
                        <span class="ticker__value">${{ number_format($ticker[$coin]['price'] ?? 0, 0) }}</span>
                        <span class="ticker__change {{ $up ? 'crypto-change--up' : 'crypto-change--down' }}">{{ $up ? '▲' : '▼' }} {{ number_format(abs($ticker[$coin]['change'] ?? 0), 2) }}%</span>
                    </a>
                @endif
            @endforeach
            @foreach(['usd', 'eur'] as $cur)
                @if($ticker[$cur])
                    @php $cup = ($ticker[$cur]['change'] ?? 0) >= 0; @endphp
                    <a href="{{ route('currency.index') }}" class="ticker__item">
                        <span class="ticker__label">{{ $ticker[$cur]['code'] }}</span>
                        <span class="ticker__value">₾{{ number_format($ticker[$cur]['per_unit'] ?? 0, 2) }}</span>
                        <span class="ticker__change {{ $cup ? 'crypto-change--up' : 'crypto-change--down' }}">{{ $cup ? '▲' : '▼' }} {{ number_format(abs($ticker[$cur]['change'] ?? 0), 2) }}%</span>
                    </a>
                @endif
            @endforeach
            @if($ticker['headline'])
                <a href="{{ route('news.show', $ticker['headline']) }}" class="ticker__item ticker__item--headline">
                    <span class="ticker__label">LATEST</span>
                    <span class="ticker__headline">{{ strtoupper(\Illuminate\Support\Str::limit($ticker['headline']->title, 70)) }}</span>
                </a>
            @endif
        </div>
    @endif

    @if(!$hero)
        <div class="empty-state">
            <h2 class="empty-state__title">NO DISPATCHES YET</h2>
            <p class="empty-state__text">The pressroom is quiet. Check back soon for the latest dispatches.</p>
        </div>
    @else
        @php
            $heroCategory = strtolower($hero->category ?? '');
            $heroClass = $categoryClassMap[$heroCategory] ?? 'category--tech';
            $heroImage = $hero->imageSource() ?: $fallbackImage;
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

        <div class="section-header">
            <h3 class="section-title">LATEST DISPATCHES</h3>
            <div class="section-line"></div>
            <form class="dispatch-search" action="{{ route('news.index') }}" method="GET">
                <input class="dispatch-search__input" type="text" name="search" value="{{ $search }}" placeholder="SEARCH DISPATCHES">
                <button type="submit" class="dispatch-search__btn">SEARCH</button>
                @if($search)
                    <a href="{{ route('news.index') }}" class="dispatch-search__clear">CLEAR</a>
                @endif
            </form>
        </div>

        @if($dispatches->isEmpty())
            <div class="dispatch-search__empty">
                <p>No dispatches found matching your search.</p>
            </div>
        @else
            <section class="news-grid">
                @foreach($dispatches as $news)
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
