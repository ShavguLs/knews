@extends('layouts.app')

@section('title', 'CURRENCY | KNEWS')

@php
    $gel = function ($v) {
        if ($v === null) return '—';
        return '₾ ' . number_format($v, 4);
    };
    $usd = function ($v) {
        if ($v === null) return '—';
        return number_format($v, 4);
    };
    $change = function ($v) {
        if ($v === null) return ['—', ''];
        $cls = $v >= 0 ? 'crypto-change--up' : 'crypto-change--down';
        $arrow = $v >= 0 ? '▲' : '▼';
        return [$arrow . ' ' . number_format(abs($v), 2) . '%', $cls];
    };
@endphp

@section('content')
    <div class="section-header">
        <h3 class="section-title">CURRENCY · GEL</h3>
        <div class="section-line"></div>
        @if($updatedAt)
            <span class="section-issue">UPDATED {{ strtoupper($updatedAt) }}</span>
        @endif
    </div>

    @if($error)
        <div class="empty-state">
            <h2 class="empty-state__title">RATES UNAVAILABLE</h2>
            <p class="empty-state__text">The National Bank wire is down. Check back shortly for the latest rates.</p>
        </div>
    @else
        @if($spotlight)
            @php [$spotChange, $spotClass] = $change($spotlight['change']); @endphp
            <section class="weather-spotlight">
                <div class="weather-spotlight__content">
                    <span class="weather-spotlight__kicker">OFFICIAL RATE</span>
                    <h2 class="weather-spotlight__city">{{ strtoupper($spotlight['name']) }}</h2>
                    <span class="weather-spotlight__region">{{ $spotlight['code'] }} · NATIONAL BANK OF GEORGIA</span>
                    <p class="weather-spotlight__condition">1 {{ $spotlight['code'] }} = {{ $gel($spotlight['per_unit']) }} <span class="crypto-change {{ $spotClass }}">{{ $spotChange }}</span></p>

                    <div class="weather-spotlight__stats">
                        <div class="weather-stat">
                            <span class="weather-stat__label">1 GEL =</span>
                            <span class="weather-stat__value">{{ $usd($spotlight['inverse']) }} {{ $spotlight['code'] }}</span>
                        </div>
                        <div class="weather-stat">
                            <span class="weather-stat__label">24H CHANGE</span>
                            <span class="weather-stat__value crypto-change {{ $spotClass }}">{{ $spotChange }}</span>
                        </div>
                    </div>
                </div>

                <div class="weather-spotlight__reading">
                    <div class="currency-glyph">₾</div>
                    <span class="weather-spotlight__temp">{{ number_format($spotlight['per_unit'], 4) }}</span>
                    <span class="air-caption">GEL PER {{ $spotlight['code'] }}</span>
                </div>
            </section>
        @endif

        @if(!empty($currencies))
            <div class="section-header">
                <h3 class="section-title">EXCHANGE RATES</h3>
                <div class="section-line"></div>
            </div>

            <section class="weather-grid">
                @foreach($currencies as $cur)
                    @php [$curChange, $curClass] = $change($cur['change']); @endphp
                    <article class="weather-card">
                        <div class="weather-card__header">
                            <div>
                                <h4 class="weather-card__city">{{ $cur['code'] }}</h4>
                                <span class="weather-card__region">{{ strtoupper($cur['name']) }}</span>
                            </div>
                        </div>
                        <div class="weather-card__body">
                            <span class="weather-card__temp">{{ $gel($cur['per_unit']) }}</span>
                            <span class="crypto-change {{ $curClass }}">{{ $curChange }}</span>
                        </div>
                        <div class="crypto-card__stats">
                            <div class="crypto-card__stat">
                                <span class="crypto-card__stat-label">1 GEL</span>
                                <span class="crypto-card__stat-value">{{ $usd($cur['inverse']) }}</span>
                            </div>
                            <div class="crypto-card__stat">
                                <span class="crypto-card__stat-label">QUOTED</span>
                                <span class="crypto-card__stat-value">{{ $cur['updated_at'] ?? '—' }}</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>
        @endif
    @endif
@endsection
