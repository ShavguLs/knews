@extends('layouts.app')

@section('title', 'AIR QUALITY | KNEWS')

@section('content')
    <div class="section-header">
        <h3 class="section-title">GEORGIA AIR QUALITY</h3>
        <div class="section-line"></div>
        @if($updatedAt)
            <span class="section-issue">UPDATED {{ strtoupper($updatedAt) }}</span>
        @endif
    </div>

    @if($error)
        <div class="empty-state">
            <h2 class="empty-state__title">READINGS UNAVAILABLE</h2>
            <p class="empty-state__text">The air-quality wire is down. Check back shortly for the latest readings.</p>
        </div>
    @else
        @if($spotlight)
            <section class="weather-spotlight">
                <div class="weather-spotlight__content">
                    <span class="weather-spotlight__kicker">SPOTLIGHT</span>
                    <h2 class="weather-spotlight__city">{{ strtoupper($spotlight['name']) }}</h2>
                    <span class="weather-spotlight__region">{{ $spotlight['region'] }}, GEORGIA</span>
                    <p class="weather-spotlight__condition">AIR QUALITY <span class="aqi-badge {{ $spotlight['class'] }}">{{ $spotlight['label'] }}</span></p>

                    <div class="weather-spotlight__stats">
                        <div class="weather-stat">
                            <span class="weather-stat__label">PM2.5</span>
                            <span class="weather-stat__value">{{ $spotlight['pm25'] !== null ? $spotlight['pm25'] : '—' }}</span>
                        </div>
                        <div class="weather-stat">
                            <span class="weather-stat__label">PM10</span>
                            <span class="weather-stat__value">{{ $spotlight['pm10'] !== null ? $spotlight['pm10'] : '—' }}</span>
                        </div>
                        <div class="weather-stat">
                            <span class="weather-stat__label">OZONE</span>
                            <span class="weather-stat__value">{{ $spotlight['ozone'] !== null ? $spotlight['ozone'] : '—' }}</span>
                        </div>
                        <div class="weather-stat">
                            <span class="weather-stat__label">NO₂</span>
                            <span class="weather-stat__value">{{ $spotlight['no2'] !== null ? $spotlight['no2'] : '—' }}</span>
                        </div>
                    </div>
                </div>

                <div class="weather-spotlight__reading">
                    <span class="aqi-badge aqi-badge--lg {{ $spotlight['class'] }}">{{ $spotlight['label'] }}</span>
                    <span class="weather-spotlight__temp">{{ $spotlight['aqi'] !== null ? $spotlight['aqi'] : '—' }}</span>
                    <span class="air-caption">US AQI</span>
                </div>
            </section>
        @endif

        @if(!empty($cities))
            <div class="section-header">
                <h3 class="section-title">ACROSS GEORGIA</h3>
                <div class="section-line"></div>
            </div>

            <section class="weather-grid">
                @foreach($cities as $city)
                    <article class="weather-card">
                        <div class="weather-card__header">
                            <div>
                                <h4 class="weather-card__city">{{ strtoupper($city['name']) }}</h4>
                                <span class="weather-card__region">{{ $city['region'] }}</span>
                            </div>
                            <span class="aqi-badge {{ $city['class'] }}">{{ $city['label'] }}</span>
                        </div>
                        <div class="weather-card__body">
                            <span class="weather-card__temp">{{ $city['aqi'] !== null ? $city['aqi'] : '—' }}</span>
                            <span class="air-caption">US AQI</span>
                        </div>
                        <div class="crypto-card__stats">
                            <div class="crypto-card__stat">
                                <span class="crypto-card__stat-label">PM2.5</span>
                                <span class="crypto-card__stat-value">{{ $city['pm25'] !== null ? $city['pm25'] : '—' }}</span>
                            </div>
                            <div class="crypto-card__stat">
                                <span class="crypto-card__stat-label">PM10</span>
                                <span class="crypto-card__stat-value">{{ $city['pm10'] !== null ? $city['pm10'] : '—' }}</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>
        @endif
    @endif
@endsection
