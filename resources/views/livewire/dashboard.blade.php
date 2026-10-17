<div>
    <div class="app-header">
        <div>
            <div class="greeting-name">Hi, {{ $displayName }} 👋</div>
            <div class="greeting-sub">Small Bits, big progress.</div>
        </div>
        <div class="avatar-tile">{{ strtoupper(substr($displayName, 0, 1)) }}</div>
    </div>

    {{-- Weekly progress --}}
    <div class="score-card">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
            <span class="sec-label">This week</span>
            <span style="font-size:28px; font-weight:700; color:var(--primary); letter-spacing:-1px;">{{ $weekPct }}%</span>
        </div>
        <div class="progress-bar"><div class="progress-bar__fill" style="width:{{ $weekPct }}%"></div></div>
    </div>

    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
        <span class="sec-label">Today</span>
        <a href="{{ route('bits.create') }}" wire:navigate style="font-size:12px; font-weight:600; color:var(--primary);">+ New Bit</a>
    </div>

    <div style="display:flex; flex-direction:column; gap:10px;">
        @forelse ($rows as $row)
            <div class="bit-card {{ $row['doneToday'] ? 'bit-card--done' : '' }}">
                <div class="bit-icon">✨</div>
                <div style="flex:1; min-width:0;">
                    <div class="bit-title" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $row['title'] }}</div>
                    <div class="bit-meta">{{ $row['doneThisWeek'] }}/{{ $row['goal'] }} this week</div>
                </div>
                <button wire:click="toggle('{{ $row['id'] }}')" class="check-circle {{ $row['doneToday'] ? 'is-done' : '' }}" aria-label="Toggle done">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </button>
            </div>
        @empty
            <div class="card" style="text-align:center; padding:40px 20px; color:var(--text-meta);">
                <div style="font-size:40px; margin-bottom:12px;">✨</div>
                <p style="font-size:13px; margin-bottom:16px;">No Bits yet — create your first one!</p>
                <a href="{{ route('bits.create') }}" wire:navigate class="btn btn--grad" style="max-width:200px; margin:0 auto;">Create a Bit</a>
            </div>
        @endforelse
    </div>
</div>
