<div wire:key="dash-{{ $selectedLabel }}">
    {{-- Top bar: avatar · greeting · bell --}}
    <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
        <a href="{{ route('profile') }}" wire:navigate class="avatar-tile" aria-label="Profile">
            {{ strtoupper(substr($first, 0, 1)) }}
        </a>
        <div style="flex:1; min-width:0;">
            <div style="font-size:12px; color:var(--text-meta); line-height:1;">Good morning</div>
            <div class="greeting-name" style="font-size:19px; line-height:1.2; margin-top:3px;">{{ $first }}</div>
        </div>
        <button type="button" class="bell-btn" aria-label="Notifications">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
        </button>
    </div>

    {{-- Week navigator --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
        <button type="button" wire:click="prevWeek" class="nav-btn" aria-label="Previous week">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
        <div style="text-align:center;">
            <div style="font-size:14px; font-weight:600; color:var(--text);">{{ $weekLabel }}</div>
            <div style="font-size:11px; color:var(--text-meta); margin-top:1px;">{{ $offsetLabel }}</div>
        </div>
        <button type="button" wire:click="nextWeek" class="nav-btn" @disabled(! $canNext) aria-label="Next week" @style(['opacity:.35; cursor:not-allowed' => ! $canNext])>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
    </div>

    {{-- Day strip --}}
    <div style="display:grid; grid-template-columns:repeat(7,1fr); gap:6px; margin-bottom:22px;">
        @foreach ($strip as $d)
            <button type="button" wire:click="selectDay('{{ $d['iso'] }}')" style="display:flex; flex-direction:column; align-items:center; gap:6px; background:none; border:none; cursor:pointer; padding:0;">
                <span style="font-size:11px; font-weight:600; color:var(--text-meta);">{{ $d['dow'] }}</span>
                <span class="day-circle @if($d['isSelected']) is-selected @elseif($d['isToday']) is-today @endif">
                    {{ $d['day'] }}
                    @if($d['hasDone'])<span class="day-dot"></span>@endif
                </span>
            </button>
        @endforeach
    </div>

    {{-- Circular hero score card --}}
    <div class="hero-card" style="margin-bottom:26px;">
        <div style="position:relative; width:100px; height:100px; flex-shrink:0;">
            <svg width="100" height="100" style="transform:rotate(-90deg);">
                <circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,.25)" stroke-width="8"/>
                <circle cx="50" cy="50" r="40" fill="none" stroke="#fff" stroke-width="8" stroke-linecap="round"
                    stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $dashoffset }}"
                    style="transition:stroke-dashoffset .6s ease;"/>
            </svg>
            <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; flex-direction:column; color:#fff;">
                <span style="font-size:24px; font-weight:800; line-height:1;">{{ $weekPct }}%</span>
            </div>
        </div>
        <div style="color:#fff; flex:1; min-width:0;">
            <div style="font-size:15px; font-weight:700;">{{ $offsetLabel }}</div>
            <div style="font-size:12px; opacity:.85; margin-top:3px;">{{ $doneSum }} done · {{ max($targetSum - $doneSum, 0) }} left</div>
            <div class="trend-pill" style="margin-top:10px;">
                @if($weekPct >= 100) 🎉 Goal smashed
                @elseif($weekPct >= 60) 🔥 On track
                @elseif($weekPct > 0) 💪 Keep going
                @else ✨ Fresh start
                @endif
            </div>
        </div>
    </div>

    {{-- Today's Bits heading --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
        <div class="greeting-name" style="font-size:16px;">
            @if($isSelectedToday) Today's Bits @else {{ $selectedLabel }} @endif
        </div>
        <a href="{{ route('bits.index') }}" wire:navigate style="font-size:13px; font-weight:600; color:var(--primary);">View all</a>
    </div>

    {{-- Motivating one-liner --}}
    @if($totalCount > 0)
        <div style="font-size:13px; margin-bottom:14px; color:{{ $doneCount >= $totalCount ? 'var(--green,#22C55E)' : 'var(--text-meta)' }};">
            @if($doneCount >= $totalCount) 🎉 All done — nice work! @else {{ $doneCount }} of {{ $totalCount }} done 💪 @endif
        </div>
    @endif

    {{-- "This week · by bit" --}}
    <div class="sec-label" style="margin-bottom:10px;">This week · by bit</div>

    {{-- Bit cards --}}
    @forelse ($todayBits as $bit)
        <div class="bit-card" wire:key="bit-{{ $bit['id'] }}" style="margin-bottom:10px;">
            <div class="bit-icon">{{ $bit['emoji'] }}</div>
            <div style="flex:1; min-width:0;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span class="bit-title">{{ $bit['title'] }}</span>
                </div>
                <div style="font-size:11px; color:var(--text-meta); margin-top:2px;">{{ $bit['freq'] }} · {{ $bit['weekDone'] }}/{{ $bit['weekGoal'] }} this week</div>
                <div class="progress-bar" style="margin-top:7px;">
                    <span class="progress-bar__fill" style="width:{{ $bit['pct'] }}%;"></span>
                </div>
            </div>
            <button type="button" wire:click="toggle('{{ $bit['id'] }}')" @disabled($isSelectedFuture)
                class="check-circle @if($bit['doneSelected']) is-done @endif" aria-label="Toggle {{ $bit['title'] }}">
                @if($bit['doneSelected'])
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                @endif
            </button>
        </div>
    @empty
        <div class="card" style="text-align:center; padding:40px 20px; color:var(--text-meta);">
            <div style="font-size:36px; margin-bottom:10px;">🌱</div>
            <p style="font-size:14px; font-weight:600; color:var(--text); margin-bottom:4px;">No Bits yet</p>
            <p style="font-size:12px; margin-bottom:16px;">Create your first habit to start tracking.</p>
            <a href="{{ route('bits.create') }}" wire:navigate class="btn btn--grad" style="display:inline-block; text-decoration:none; width:auto; padding:0 20px;">+ New Bit</a>
        </div>
    @endforelse

    {{-- Daily Check-In card --}}
    @if($totalCount > 0)
        <div class="card" style="display:flex; align-items:center; gap:12px; margin-top:16px; background:var(--surface-tint);">
            <div class="bit-icon" style="background:rgba(91,127,240,.12);">☀️</div>
            <div style="flex:1; min-width:0;">
                <div class="bit-title">Daily Check-In</div>
                <div style="font-size:11px; color:var(--text-meta); margin-top:2px;">
                    {{ $checkinDone ? 'Checked in for this day' : 'Complete a bit to check in' }}
                </div>
            </div>
            <div class="check-circle @if($checkinDone) is-done @endif" aria-hidden="true" style="cursor:default;">
                @if($checkinDone)
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                @endif
            </div>
        </div>
    @endif
</div>
