<!DOCTYPE html>
<html lang="en">
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
    <main class="phone-frame" style="padding:40px 28px 48px; display:flex; flex-direction:column; min-height:100vh;">
        <div style="margin-bottom:28px; display:flex; align-items:center; gap:10px;">
            <span style="width:34px; height:34px; border-radius:11px; background:var(--grad); display:flex; align-items:center; justify-content:center; font-size:19px; box-shadow:0 4px 12px rgba(91,141,239,.28);">✨</span>
            <span style="font-size:22px; font-weight:800; letter-spacing:-.6px; color:var(--text);">Sparx Bit</span>
        </div>

        {{ $slot }}
    </main>
    @livewireScripts
</body>
</html>
