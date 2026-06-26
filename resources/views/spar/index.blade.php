@extends('layouts.app')

@section('title', 'SPAR · KUTAISI | KNEWS')

@php
    $money = fn($v) => $v === null ? '—' : '₾' . number_format($v, 2);
    $pageUrl = function ($n) use ($selectedCategory, $search) {
        $params = ['page' => $n];
        if ($selectedCategory !== '') $params['category'] = $selectedCategory;
        if ($search !== '') $params['search'] = $search;
        return route('spar.index', $params);
    };
@endphp

@section('content')
    <div class="section-header">
        <h3 class="section-title">SPAR · KUTAISI</h3>
        <div class="section-line"></div>
        @if(!$error)
            <span class="section-issue">{{ number_format($total) }} {{ ($search !== '' || $selectedCategory !== '') ? 'MATCHES' : 'DISCOUNTED ITEMS' }}</span>
        @endif
    </div>

    <form method="GET" action="{{ route('spar.index') }}" class="shop-filter">
        <input type="text" name="search" value="{{ $search }}" class="shop-filter__search" placeholder="SEARCH ITEMS">
        <select name="category" class="shop-filter__select" onchange="this.form.submit()">
            <option value="">ALL CATEGORIES</option>
            @foreach($categories as $slug => $label)
                <option value="{{ $slug }}" {{ $selectedCategory === $slug ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="shop-filter__btn">SEARCH</button>
        @if($search !== '' || $selectedCategory !== '')
            <a href="{{ route('spar.index') }}" class="shop-filter__clear">CLEAR</a>
        @endif
    </form>

    @if($error)
        <div class="empty-state">
            <h2 class="empty-state__title">PRICES UNAVAILABLE</h2>
            <p class="empty-state__text">The price feed is down. Check back shortly for the latest Spar deals.</p>
        </div>
    @elseif(empty($products))
        <div class="empty-state">
            <h2 class="empty-state__title">NO MATCHES</h2>
            <p class="empty-state__text">No Spar items matched your filters. Try a different search or category.</p>
        </div>
    @else
        <section class="product-grid">
            @foreach($products as $p)
                <article class="product-card">
                    <div class="product-card__image-frame">
                        @if($p['discount'])
                            <span class="product-card__discount">-{{ $p['discount'] }}%</span>
                        @endif
                        @if($p['image'])
                            <img src="{{ $p['image'] }}" alt="{{ $p['name'] }}" loading="lazy">
                        @else
                            <div class="product-card__noimage">SPAR</div>
                        @endif
                    </div>
                    <div class="product-card__body">
                        @if($p['category'])
                            <span class="product-card__category">{{ $p['category'] }}</span>
                        @endif
                        <h4 class="product-card__name">{{ $p['name'] }}</h4>
                        <div class="product-card__prices">
                            <span class="product-card__sale">{{ $money($p['price']) }}</span>
                            @if($p['on_sale'])
                                <span class="product-card__original">{{ $money($p['original']) }}</span>
                            @endif
                        </div>
                        @if($p['unit'])
                            <span class="product-card__unit">{{ $p['unit'] }}</span>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>

        @if($pages > 1)
            @php
                $start = max(1, $page - 2);
                $end = min($pages, $page + 2);
            @endphp
            <nav class="pagination">
                <span class="pagination__info">PAGE {{ $page }} OF {{ $pages }}</span>

                @if($page > 1)
                    <a href="{{ $pageUrl(1) }}" class="pagination__link">« FIRST</a>
                    <a href="{{ $pageUrl($page - 1) }}" class="pagination__link">PREV</a>
                @else
                    <span class="pagination__disabled">« FIRST</span>
                    <span class="pagination__disabled">PREV</span>
                @endif

                @for($i = $start; $i <= $end; $i++)
                    @if($i === $page)
                        <span class="pagination__current">{{ $i }}</span>
                    @else
                        <a href="{{ $pageUrl($i) }}" class="pagination__link">{{ $i }}</a>
                    @endif
                @endfor

                @if($page < $pages)
                    <a href="{{ $pageUrl($page + 1) }}" class="pagination__link">NEXT</a>
                    <a href="{{ $pageUrl($pages) }}" class="pagination__link">LAST »</a>
                @else
                    <span class="pagination__disabled">NEXT</span>
                    <span class="pagination__disabled">LAST »</span>
                @endif
            </nav>
        @endif
    @endif
@endsection
