@extends('layouts.app')

@section('title', 'WEATHER | KNEWS')

@section('content')
    <div class="section-header">
        <h3 class="section-title">GEORGIA WEATHER</h3>
        <div class="section-line"></div>
        @if($updatedAt)
            <span class="section-issue">UPDATED {{ strtoupper($updatedAt) }}</span>
        @endif
    </div>

    @if($error)
        <div class="empty-state">
            <h2 class="empty-state__title">FORECAST UNAVAILABLE</h2>
            <p class="empty-state__text">The weather wire is down. Check back shortly for the latest readings.</p>
        </div>
    @else
        @if($spotlight)
            <section class="weather-spotlight">
                <div class="weather-spotlight__content">
                    <span class="weather-spotlight__kicker">SPOTLIGHT</span>
                    <h2 class="weather-spotlight__city">{{ strtoupper($spotlight['name']) }}</h2>
                    <span class="weather-spotlight__region">{{ $spotlight['region'] }}, GEORGIA</span>
                    <p class="weather-spotlight__condition">{{ $spotlight['label'] }}</p>

                    <div class="weather-spotlight__stats">
                        <div class="weather-stat">
                            <span class="weather-stat__label">FEELS LIKE</span>
                            <span class="weather-stat__value">{{ $spotlight['feels'] !== null ? $spotlight['feels'] . '°' : '—' }}</span>
                        </div>
                        <div class="weather-stat">
                            <span class="weather-stat__label">HUMIDITY</span>
                            <span class="weather-stat__value">{{ $spotlight['humidity'] !== null ? $spotlight['humidity'] . '%' : '—' }}</span>
                        </div>
                        <div class="weather-stat">
                            <span class="weather-stat__label">WIND</span>
                            <span class="weather-stat__value">{{ $spotlight['wind'] !== null ? $spotlight['wind'] . ' KM/H' : '—' }}</span>
                        </div>
                    </div>
                </div>

                <div class="weather-spotlight__reading">
                    <span class="material-symbols-outlined weather-spotlight__icon">{{ $spotlight['icon'] }}</span>
                    <span class="weather-spotlight__temp">{{ $spotlight['temp'] !== null ? $spotlight['temp'] . '°' : '—' }}</span>
                    <div class="weather-forecast">
                        @foreach($spotlight['forecast'] as $day)
                            <div class="weather-forecast__day">
                                <span class="weather-forecast__label">{{ $day['label'] }}</span>
                                <span class="material-symbols-outlined weather-forecast__icon">{{ $day['icon'] }}</span>
                                <span class="weather-forecast__range">{{ $day['max'] !== null ? $day['max'] . '°' : '—' }} / {{ $day['min'] !== null ? $day['min'] . '°' : '—' }}</span>
                            </div>
                        @endforeach
                    </div>
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
                            <span class="material-symbols-outlined weather-card__icon">{{ $city['icon'] }}</span>
                        </div>
                        <div class="weather-card__body">
                            <span class="weather-card__temp">{{ $city['temp'] !== null ? $city['temp'] . '°' : '—' }}</span>
                            <span class="weather-card__condition">{{ $city['label'] }}</span>
                        </div>
                        <div class="weather-card__forecast">
                            @foreach($city['forecast'] as $day)
                                <div class="weather-card__day">
                                    <span class="weather-card__day-label">{{ $day['label'] }}</span>
                                    <span class="material-symbols-outlined weather-card__day-icon">{{ $day['icon'] }}</span>
                                    <span class="weather-card__day-temp">{{ $day['max'] !== null ? $day['max'] . '°' : '—' }}</span>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </section>
        @endif
    @endif
@endsection
