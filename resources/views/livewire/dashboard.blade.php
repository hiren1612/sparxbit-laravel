<div wire:key="dash-{{ $selectedLabel }}">
    {{-- Top bar: avatar · greeting · bell --}}
    <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
        <a href="{{ route('profile') }}" wire:navigate class="avatar-tile" aria-label="Profile" @if($avatarEmoji) style="font-size:24px;" @endif>
            {{ $avatarEmoji ?: strtoupper(substr($first, 0, 1)) }}
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

    {{-- Week navigator: date range left, arrows grouped right --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
        <div>
            <div style="font-size:14px; font-weight:600; color:var(--text);">{{ $weekLabel }}</div>
            <div style="font-size:11px; color:var(--text-meta); margin-top:1px;">{{ $offsetLabel }}</div>
        </div>
        <div style="display:flex; gap:8px;">
            <button type="button" wire:click="prevWeek" class="nav-btn" aria-label="Previous week">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <button type="button" wire:click="nextWeek" class="nav-btn" @disabled(! $canNext) aria-label="Next week" @style(['opacity:.35; cursor:not-allowed' => ! $canNext])>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>
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
                <span style="font-size:24px; font-weight:800; line-height:1;">{{ $targetSum === 0 ? '—' : $weekPct.'%' }}</span>
            </div>
        </div>
        <div style="color:#fff; flex:1; min-width:0;">
            <div style="font-size:16px; font-weight:800;">{{ $heroTitle }}</div>
            <div style="font-size:12px; opacity:.9; margin-top:4px; line-height:1.5;">{{ $heroSub }}</div>
            @if ($targetSum > 0)
                <div style="font-size:11px; opacity:.85; margin-top:8px;">{{ $doneSum }} done · {{ max($targetSum - $doneSum, 0) }} left</div>
            @endif
            <div class="trend-pill" style="margin-top:10px;">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-1px;"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                {{ $offsetLabel }}
            </div>
        </div>
    </div>

    {{-- Today's Bits heading --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
        <div class="greeting-name" style="font-size:16px;">
            @if($isSelectedToday) Today's Bits @else {{ $selectedLabel }} @endif
        </div>
        <a href="{{ route('bits.index') }}" wire:navigate style="font-size:12px; font-weight:500; color:var(--text-meta); display:flex; align-items:center; gap:2px;">
            View all
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
    </div>

    {{-- Motivating one-liner --}}
    @if($totalCount > 0)
        <div style="font-size:13px; margin-bottom:14px; color:{{ $doneCount >= $totalCount ? 'var(--green,#22C55E)' : 'var(--text-meta)' }};">
            @if($doneCount >= $totalCount) All done for today — nice work! 🎉 @else {{ $doneCount }} of {{ $totalCount }} done 💪 @endif
        </div>
    @endif

    {{-- "<offset> · by bit" --}}
    <div class="sec-label" style="margin-bottom:10px;">{{ $offsetLabel }} · by bit</div>

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
            @if ($isCurrentWeek)
                <p style="font-size:14px; font-weight:600; color:var(--text); margin-bottom:4px;">No Bits yet</p>
                <p style="font-size:12px; margin-bottom:16px;">Create your first habit to start tracking.</p>
                <a href="{{ route('bits.create') }}" wire:navigate class="btn btn--grad" style="display:inline-block; text-decoration:none; width:auto; padding:0 20px;">+ New Bit</a>
            @else
                <p style="font-size:14px; font-weight:600; color:var(--text); margin-bottom:4px;">No Bits on this day</p>
                <p style="font-size:12px;">You had no active Bits scheduled here.</p>
            @endif
        </div>
    @endforelse

    {{-- Mark all done today --}}
    @if ($markAllCount > 0)
        <button type="button" wire:click="markAll"
            style="width:100%; height:42px; border-radius:12px; background:var(--surface); border:1px dashed var(--primary-mid); color:var(--primary); font-size:12.5px; font-weight:600; cursor:pointer; font-family:inherit; margin:14px 0 4px; display:flex; align-items:center; justify-content:center; gap:8px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            Mark all {{ $markAllCount }} done today
        </button>
    @endif

    {{-- Daily Check-In card --}}
    <div class="card" style="display:flex; align-items:center; gap:12px; margin-top:16px; background:var(--surface-tint); position:relative; overflow:hidden;">
        <span style="position:absolute; left:0; top:0; bottom:0; width:4px; background:var(--grad);"></span>
        <div class="bit-icon" style="background:rgba(91,127,240,.12);">✨</div>
        <div style="flex:1; min-width:0;">
            <div style="display:flex; align-items:center; gap:7px;">
                <div class="bit-title">Daily Check-In</div>
                <span style="font-size:9px; font-weight:700; letter-spacing:.4px; text-transform:uppercase; color:var(--primary); background:var(--primary-lt); padding:2px 6px; border-radius:999px;">Auto</span>
            </div>
            <div style="font-size:11px; color:var(--text-meta); margin-top:2px;">
                {{ $checkinDone ? 'Opened today' : 'Open the app to count today' }}
            </div>
        </div>
        @if ($checkinDone)
            <div class="check-circle is-done" aria-hidden="true" style="cursor:default;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
        @else
            <div class="check-circle" aria-hidden="true" style="cursor:default; color:var(--text-meta);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/></svg>
            </div>
        @endif
    </div>

    {{-- Paused Bits --}}
    @if ($paused->isNotEmpty())
        <div style="margin-top:24px;">
            <div style="display:flex; align-items:center; gap:6px; margin-bottom:10px;">
                <span style="font-size:14px; font-weight:600; color:var(--text);">Ready when you are</span>
                <span style="font-size:11px; color:var(--text-meta);">· {{ $paused->count() }} paused</span>
            </div>
            <div style="display:flex; flex-direction:column; gap:8px;">
                @foreach ($paused as $p)
                    <div wire:key="paused-{{ $p['id'] }}" style="background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:12px 14px; display:flex; align-items:center; gap:12px; box-shadow:var(--sh-sm);">
                        <a href="{{ route('bits.show', $p['id']) }}" wire:navigate style="display:flex; align-items:center; gap:12px; flex:1; min-width:0; text-decoration:none;">
                            <span style="font-size:20px; opacity:.55;">{{ $p['emoji'] }}</span>
                            <div style="flex:1; min-width:0;">
                                <div style="font-size:13.5px; font-weight:600; color:var(--text); margin-bottom:2px;">{{ $p['title'] }}</div>
                                <div style="font-size:11px; color:var(--text-meta);">{{ $p['ago'] }}@if($p['doneCount'] > 0) · {{ $p['doneCount'] }} check-in{{ $p['doneCount'] === 1 ? '' : 's' }} before pause @endif</div>
                            </div>
                        </a>
                        <button type="button" wire:click="resume('{{ $p['id'] }}')" style="background:var(--primary); color:#fff; border:none; border-radius:10px; padding:8px 14px; font-size:12px; font-weight:600; cursor:pointer; font-family:inherit; box-shadow:var(--sh-btn);">Resume</button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Overview --}}
    @if ($isCurrentWeek)
        <div style="display:flex; align-items:center; justify-content:space-between; margin:24px 0 4px;">
            <div style="font-size:15px; font-weight:600; color:var(--text);">Overview</div>
            <a href="{{ route('stats') }}" wire:navigate style="font-size:12px; font-weight:500; color:var(--text-meta); display:flex; align-items:center; gap:2px;">
                See all
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>
        @php
            $parts = [];
            if ($overview['active'] > 0) $parts[] = $overview['active'].' active';
            if ($overview['paused'] > 0) $parts[] = $overview['paused'].' paused';
            if ($overview['upcoming'] > 0) $parts[] = $overview['upcoming'].' upcoming';
            if ($overview['completed'] > 0) $parts[] = $overview['completed'].' completed';
        @endphp
        @if (count($parts) > 0)
            <div style="font-size:11px; color:var(--text-meta); margin-bottom:10px;">{{ implode(' · ', $parts) }}</div>
        @endif
        <div style="display:flex; gap:8px;">
            @foreach ([['onTrack', 'On track'], ['reviewing', 'In progress'], ['paused', 'Paused'], ['completed', 'Completed']] as [$k, $lbl])
                <div style="flex:1; min-width:0; background:var(--surface); border-radius:14px; border:1px solid var(--border); padding:12px 10px 10px; box-shadow:var(--sh-sm);">
                    <div style="font-size:18px; font-weight:700; color:var(--text); letter-spacing:-.4px; line-height:1; margin-bottom:4px;">{{ $overview[$k] }}</div>
                    <div style="font-size:10.5px; font-weight:500; color:var(--text-meta);">{{ $lbl }}</div>
                </div>
            @endforeach
        </div>
    @endif
</div>
