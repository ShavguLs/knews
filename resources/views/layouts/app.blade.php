<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'KNEWS | THE FINAL WORD IN JOURNALISM')</title>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,200..800;1,6..72,200..800&family=Work+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="page">
    <header class="site-header">
        <div class="site-header__brand">
            <h1 class="site-header__title"><a href="{{ route('news.index') }}">KNEWS</a></h1>
        </div>

        <button class="site-header__toggle" type="button" aria-controls="site-menu" aria-expanded="false" aria-label="Open navigation" data-site-menu-toggle>
            <span class="material-symbols-outlined site-header__toggle-icon" aria-hidden="true">menu</span>
        </button>

        <div class="site-header__menu" id="site-menu" data-site-menu>
            <nav class="site-header__nav" aria-label="Primary navigation">
                <a href="{{ route('news.index') }}" class="nav-link {{ request()->routeIs('news.index') ? 'nav-link--active' : '' }}">HOME</a>
                <a href="{{ route('weather.index') }}" class="nav-link {{ request()->routeIs('weather.index') ? 'nav-link--active' : '' }}">WEATHER</a>
                <a href="{{ route('crypto.index') }}" class="nav-link {{ request()->routeIs('crypto.index') ? 'nav-link--active' : '' }}">CRYPTO</a>
                <a href="{{ route('currency.index') }}" class="nav-link {{ request()->routeIs('currency.index') ? 'nav-link--active' : '' }}">CURRENCY</a>
                <a href="{{ route('air.index') }}" class="nav-link {{ request()->routeIs('air.index') ? 'nav-link--active' : '' }}">AIR</a>
                <a href="{{ route('spar.index') }}" class="nav-link {{ request()->routeIs('spar.index') ? 'nav-link--active' : '' }}">SPAR</a>
            </nav>

            <div class="site-header__actions">
                @auth
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.news.index') }}" class="subscribe-button">PANEL</a>
                    @endif
                    <a href="{{ route('bookmarks.index') }}" class="subscribe-button">SAVED</a>
                    <form action="{{ route('logout') }}" method="POST" class="site-header__logout-form">
                        @csrf
                        <button type="submit" class="subscribe-button">LOGOUT</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="subscribe-button">LOGIN</a>
                    <a href="{{ route('register') }}" class="subscribe-button">REGISTER</a>
                @endauth
            </div>
        </div>
    </header>

    @if(session('success'))
        <div class="form-panel__message" style="margin:16px 24px 0;">{{ session('success') }}</div>
    @endif

    <main class="page-main">
        @yield('content')
    </main>
</body>
</html>
