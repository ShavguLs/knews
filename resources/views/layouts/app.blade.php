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
        <nav class="site-header__nav">
            <a class="nav-link @if(!request()->is('admin*')) nav-link--active @endif" href="{{ route('news.index') }}">MAIN</a>
        </nav>
        <div class="site-header__actions">
            @auth
                <form action="{{ route('admin.logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="subscribe-button">LOGOUT</button>
                </form>
            @else
                <a href="{{ route('admin.login') }}" class="subscribe-button">ADMIN LOGIN</a>
            @endauth
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