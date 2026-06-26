@extends('layouts.app')

@section('title', 'CRYPTO | KNEWS')

@php
    $money = function ($v) {
        if ($v === null) return '—';
        if ($v >= 1) return '$' . number_format($v, 2);
        return '$' . number_format($v, 4);
    };
    $big = function ($v) {
        if ($v === null) return '—';
        if ($v >= 1e9) return '$' . number_format($v / 1e9, 2) . 'B';
        if ($v >= 1e6) return '$' . number_format($v / 1e6, 2) . 'M';
        return '$' . number_format($v);
    };
    $change = function ($v) {
        if ($v === null) return ['—', '', ''];
        $cls = $v >= 0 ? 'crypto-change--up' : 'crypto-change--down';
        $arrow = $v >= 0 ? '▲' : '▼';
        return [$arrow . ' ' . number_format(abs($v), 2) . '%', $cls, $arrow];
    };
@endphp

@section('content')
    <div class="section-header">
        <h3 class="section-title">CRYPTO MARKETS</h3>
        <div class="section-line"></div>
        @if($updatedAt)
            <span class="section-issue">UPDATED {{ strtoupper($updatedAt) }}</span>
        @endif
    </div>

    @if($error)
        <div class="empty-state">
            <h2 class="empty-state__title">MARKETS UNAVAILABLE</h2>
            <p class="empty-state__text">The market wire is down. Check back shortly for the latest prices.</p>
        </div>
    @else
        @if($spotlight)
            @php [$spotChange, $spotClass] = $change($spotlight['change']); @endphp
            <section class="weather-spotlight">
                <div class="weather-spotlight__content">
                    <span class="weather-spotlight__kicker">FLAGSHIP</span>
                    <h2 class="weather-spotlight__city">{{ strtoupper($spotlight['name']) }}</h2>
                    <span class="weather-spotlight__region">{{ $spotlight['symbol'] }}@if($spotlight['rank']) · RANK #{{ $spotlight['rank'] }}@endif</span>
                    <p class="weather-spotlight__condition">24H CHANGE <span class="crypto-change {{ $spotClass }}">{{ $spotChange }}</span></p>

                    <div class="weather-spotlight__stats">
                        <div class="weather-stat">
                            <span class="weather-stat__label">24H HIGH</span>
                            <span class="weather-stat__value">{{ $money($spotlight['high']) }}</span>
                        </div>
                        <div class="weather-stat">
                            <span class="weather-stat__label">24H LOW</span>
                            <span class="weather-stat__value">{{ $money($spotlight['low']) }}</span>
                        </div>
                        <div class="weather-stat">
                            <span class="weather-stat__label">MARKET CAP</span>
                            <span class="weather-stat__value">{{ $big($spotlight['market_cap']) }}</span>
                        </div>
                        <div class="weather-stat">
                            <span class="weather-stat__label">VOLUME</span>
                            <span class="weather-stat__value">{{ $big($spotlight['volume']) }}</span>
                        </div>
                    </div>
                </div>

                <div class="weather-spotlight__reading">
                    @if($spotlight['image'])
                        <div class="crypto-logo crypto-logo--lg">
                            <img src="{{ $spotlight['image'] }}" alt="{{ $spotlight['name'] }}">
                        </div>
                    @endif
                    <span class="weather-spotlight__temp">{{ $money($spotlight['price']) }}</span>
                    <span class="crypto-change crypto-change--block {{ $spotClass }}">{{ $spotChange }}</span>
                </div>
            </section>
        @endif

        @if(!empty($coins))
            <div class="section-header">
                <h3 class="section-title">TOP COINS</h3>
                <div class="section-line"></div>
            </div>

            <section class="weather-grid">
                @foreach($coins as $coin)
                    @php [$coinChange, $coinClass] = $change($coin['change']); @endphp
                    <article class="weather-card">
                        <div class="weather-card__header">
                            <div>
                                <h4 class="weather-card__city">{{ $coin['symbol'] }}</h4>
                                <span class="weather-card__region">{{ strtoupper($coin['name']) }}</span>
                            </div>
                            @if($coin['image'])
                                <div class="crypto-logo">
                                    <img src="{{ $coin['image'] }}" alt="{{ $coin['name'] }}">
                                </div>
                            @endif
                        </div>
                        <div class="weather-card__body">
                            <span class="weather-card__temp">{{ $money($coin['price']) }}</span>
                            <span class="crypto-change {{ $coinClass }}">{{ $coinChange }}</span>
                        </div>
                        <div class="crypto-card__stats">
                            <div class="crypto-card__stat">
                                <span class="crypto-card__stat-label">MKT CAP</span>
                                <span class="crypto-card__stat-value">{{ $big($coin['market_cap']) }}</span>
                            </div>
                            <div class="crypto-card__stat">
                                <span class="crypto-card__stat-label">VOLUME</span>
                                <span class="crypto-card__stat-value">{{ $big($coin['volume']) }}</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>
        @endif
    @endif
@endsection
