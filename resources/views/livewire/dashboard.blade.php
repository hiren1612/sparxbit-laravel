<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight">Hi, {{ $displayName }} 👋</h1>
        <p class="text-gray-500 text-sm mt-0.5">Small Bits, big progress. Here's your day.</p>
    </div>

    {{-- Weekly progress --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-gray-700">This week</span>
            <span class="text-sm font-bold text-indigo-600">{{ $weekPct }}%</span>
        </div>
        <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full bg-indigo-500 rounded-full transition-all duration-500" style="width: {{ $weekPct }}%"></div>
        </div>
    </div>

    {{-- Today --}}
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Today</h2>
        <a href="{{ route('bits.create') }}" class="text-indigo-600 text-sm font-semibold" wire:navigate>+ New Bit</a>
    </div>

    <div class="space-y-3">
        @forelse ($rows as $r)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-3 {{ $r['doneToday'] ? 'opacity-60' : '' }}">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-lg flex-shrink-0">✨</div>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-gray-900 truncate">{{ $r['title'] }}</div>
                    <div class="text-xs text-gray-500">{{ $r['doneThisWeek'] }}/{{ $r['goal'] }} this week</div>
                </div>
                <button wire:click="toggle('{{ $r['id'] }}')" aria-label="Toggle done"
                    class="w-9 h-9 rounded-full flex items-center justify-center border-2 transition flex-shrink-0
                    {{ $r['doneToday'] ? 'bg-green-500 border-green-500 text-white' : 'border-gray-300 text-transparent hover:border-indigo-400' }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </button>
            </div>
        @empty
            <div class="text-center py-12 text-gray-500">
                <div class="text-4xl mb-3">✨</div>
                <p class="mb-4 text-sm">No Bits yet — create your first one!</p>
                <a href="{{ route('bits.create') }}" class="inline-block bg-indigo-600 text-white px-5 py-2.5 rounded-xl font-semibold text-sm" wire:navigate>Create a Bit</a>
            </div>
        @endforelse
    </div>
</div>
