<div>
    <div class="app-header">
        <div class="greeting-name" style="font-size:19px;">All Bits</div>
        <a href="{{ route('bits.create') }}" wire:navigate class="btn btn--grad" style="width:auto; height:36px; padding:0 14px; font-size:12px;">+ New</a>
    </div>

    <div style="display:flex; flex-direction:column; gap:10px;">
        @forelse ($bits as $b)
            <div class="bit-card">
                <div class="bit-icon">✨</div>
                <div style="flex:1; min-width:0;">
                    <div class="bit-title" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $b->title }}</div>
                    <div class="bit-meta">
                        {{ $b->goal_per_week === 7 ? 'Daily' : $b->goal_per_week.'× / week' }} · {{ ucfirst($b->status) }}
                    </div>
                </div>
                @if ($b->is_system)
                    <span class="sec-label" style="font-size:9px;">System</span>
                @endif
            </div>
        @empty
            <div class="card" style="text-align:center; padding:40px; color:var(--text-meta); font-size:13px;">No Bits yet.</div>
        @endforelse
    </div>
</div>
