<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Tiga Serangkai') — Bootcamp & Magang Online</title>
    <meta name="description" content="Tiga Serangkai — platform bootcamp dan magang online untuk siap karier.">
    @include('partials.favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
    @include('partials.navbar')

    @if (session('success'))
        <div class="mx-auto max-w-6xl px-4 pt-4">
            <div class="rounded-xl border border-brand/30 bg-brand/10 px-4 py-3 text-sm font-medium text-brand-deeper">
                {{ session('success') }}
            </div>
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    @include('partials.footer')
</body>
</html>
