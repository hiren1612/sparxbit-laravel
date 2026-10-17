<div>
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">All Bits</h1>
        <a href="{{ route('bits.create') }}" class="bg-indigo-600 text-white text-sm font-semibold px-3 py-1.5 rounded-lg" wire:navigate>+ New</a>
    </div>

    <div class="space-y-3">
        @forelse ($bits as $b)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-lg flex-shrink-0">✨</div>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-gray-900 truncate">{{ $b->title }}</div>
                    <div class="text-xs text-gray-500">
                        {{ $b->goal_per_week === 7 ? 'Daily' : $b->goal_per_week.'× / week' }} · {{ ucfirst($b->status) }}
                    </div>
                </div>
                @if ($b->is_system)
                    <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">System</span>
                @endif
            </div>
        @empty
            <div class="text-center py-12 text-gray-500 text-sm">No Bits yet.</div>
        @endforelse
    </div>
</div>
