<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sparx Bit</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full font-sans text-gray-900 antialiased">
    <div class="min-h-full">
        <nav class="bg-white border-b border-gray-100 sticky top-0 z-10">
            <div class="max-w-md mx-auto px-4 h-14 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="text-indigo-600 font-bold text-lg" wire:navigate>✦ Sparx&nbsp;Bit</a>
                <div class="flex items-center gap-4 text-sm">
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-indigo-600 font-medium" wire:navigate>Home</a>
                    <a href="{{ route('bits.index') }}" class="text-gray-600 hover:text-indigo-600 font-medium" wire:navigate>Bits</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-400 hover:text-red-600 font-medium">Sign out</button>
                    </form>
                </div>
            </div>
        </nav>

        @if (session('status'))
            <div class="max-w-md mx-auto px-4 pt-4">
                <div class="bg-green-50 text-green-700 text-sm font-medium rounded-xl px-4 py-2 border border-green-100">
                    {{ session('status') }}
                </div>
            </div>
        @endif

        <main class="max-w-md mx-auto px-4 py-6">
            {{ $slot }}
        </main>
    </div>
    @livewireScripts
</body>
</html>
