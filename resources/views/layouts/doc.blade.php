<!DOCTYPE html>
@php $__theme = auth()->check() ? auth()->user()->theme : 'system'; @endphp
<html lang="en" @if($__theme && $__theme !== 'system') data-theme="{{ $__theme }}" @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sparx Bit</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/sparx.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
    <main class="phone-frame phone-frame--app" style="padding:0 20px 40px;">
        {{ $slot }}
    </main>
    @livewireScripts
</body>
</html>
