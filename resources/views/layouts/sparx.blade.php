@php
    $__theme = auth()->check() ? auth()->user()->theme : 'system';
@endphp
<!DOCTYPE html>
<html lang="en" @if($__theme && $__theme !== 'system') data-theme="{{ $__theme }}" @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="vapid-key" content="{{ config('services.webpush.public_key') }}">
    <meta name="user-tz" content="{{ auth()->user()?->timezone }}">
    <title>Sparx Bit</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/sparx.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
    <main class="phone-frame phone-frame--app">
        @if (session('status'))
            <div class="info-box" style="margin-bottom:14px; background:var(--green-lt); color:var(--green); font-weight:600;">
                {{ session('status') }}
            </div>
        @endif

        {{ $slot }}

        <div class="nav-space"></div>
    </main>

    @php $r = request()->route()?->getName(); @endphp
    <nav class="sparx-nav">
        <a href="{{ route('dashboard') }}" wire:navigate class="{{ $r === 'dashboard' ? 'is-active' : '' }}">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9.5 12 3l9 6.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V9.5Z"/></svg>
            <span>Home</span>
        </a>
        <a href="{{ route('calendar') }}" wire:navigate class="{{ $r === 'calendar' ? 'is-active' : '' }}">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M3 9h18M8 2v4M16 2v4"/></svg>
            <span>Calendar</span>
        </a>
        <a href="{{ route('bits.create') }}" wire:navigate class="nav-fab-link" aria-label="New Bit">
            <span class="nav-fab">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </span>
        </a>
        <a href="{{ route('stats') }}" wire:navigate class="{{ $r === 'stats' ? 'is-active' : '' }}">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20V10M10 20V4M16 20v-6M22 20H2"/></svg>
            <span>Stats</span>
        </a>
        <a href="{{ route('profile') }}" wire:navigate class="{{ $r === 'profile' ? 'is-active' : '' }}">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>
            <span>Profile</span>
        </a>
    </nav>

    @livewireScripts
</body>
</html>
