<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — Tiga Serangkai</title>
    @include('partials.favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-ink antialiased">
    <div class="mx-auto flex min-h-screen max-w-md flex-col justify-center px-5 py-10">
        @if (session('success'))
            <div class="mb-4 rounded-xl border border-brand/30 bg-brand/10 px-4 py-3 text-sm text-brand-mid">
                {{ session('success') }}
            </div>
        @endif
        @yield('content')
    </div>
</body>
</html>
