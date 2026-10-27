<div>
    <x-page-header title="Monthly Report" :back="route('profile')" />

    {{-- Month selector --}}
    <div class="no-print" style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:14px;">
        <button type="button" wire:click="prevMonth" class="hdr-back" aria-label="Previous month">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
        <div style="font-size:15px; font-weight:700; color:var(--text); letter-spacing:-.2px;">{{ $monthLabel }}</div>
        <button type="button" wire:click="nextMonth" @disabled(! $canNext) class="hdr-back" aria-label="Next month" style="{{ $canNext ? '' : 'opacity:.35; cursor:default;' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
    </div>

    {{-- The printable report --}}
    <div id="report">
        {{-- Print-only title --}}
        <div class="print-only" style="margin-bottom:12px;">
            <div style="font-size:18px; font-weight:800; color:var(--text);">Sparx Bit — Monthly Report</div>
            <div style="font-size:12px; color:var(--text-meta);">{{ $monthLabel }}</div>
        </div>

        {{-- Overall score --}}
        <div style="background:var(--grad); border-radius:18px; padding:20px; color:#fff; display:flex; align-items:center; gap:18px; margin-bottom:14px; box-shadow:var(--sh-sm);">
            @php $circ = 2 * M_PI * 34; $off = $circ - ($score / 100) * $circ; @endphp
            <svg width="84" height="84" viewBox="0 0 84 84" style="flex-shrink:0;">
                <circle cx="42" cy="42" r="34" fill="none" stroke="rgba(255,255,255,.25)" stroke-width="8"/>
                <circle cx="42" cy="42" r="34" fill="none" stroke="#fff" stroke-width="8" stroke-linecap="round"
                    stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $off }}" transform="rotate(-90 42 42)"/>
                <text x="42" y="48" text-anchor="middle" font-size="20" font-weight="800" fill="#fff">{{ $score }}%</text>
            </svg>
            <div style="min-width:0;">
                <div style="font-size:16px; font-weight:800; letter-spacing:-.3px;">Monthly completion</div>
                <div style="font-size:12px; opacity:.9; line-height:1.5; margin-top:3px;">You completed {{ $score }}% of your Bit goals in {{ $monthLabel }}.</div>
            </div>
        </div>

        {{-- Stat tiles --}}
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:16px;">
            @php
                $tiles = [
                    ['v' => $totalCompletions, 'l' => 'Completions', 'c' => 'var(--primary)'],
                    ['v' => $checkinCount.'/'.$daysInMonth, 'l' => 'Check-in days', 'c' => 'var(--green)'],
                    ['v' => $activeCount.'/'.$daysInMonth, 'l' => 'Active days', 'c' => 'var(--orange)'],
                ];
            @endphp
            @foreach ($tiles as $t)
                <div style="background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:14px 10px; text-align:center; box-shadow:var(--sh-sm);">
                    <div style="font-size:18px; font-weight:800; color:{{ $t['c'] }};">{{ $t['v'] }}</div>
                    <div style="font-size:10.5px; color:var(--text-meta); margin-top:3px;">{{ $t['l'] }}</div>
                </div>
            @endforeach
        </div>

        {{-- Per-bit breakdown --}}
        <div style="font-size:11px; font-weight:700; color:var(--text-meta); letter-spacing:.4px; text-transform:uppercase; margin-bottom:10px;">By Bit</div>
        @forelse ($rows as $r)
            @php $bar = $r['pct'] >= 100 ? 'var(--green)' : ($r['pct'] >= 50 ? 'var(--primary)' : 'var(--orange)'); @endphp
            <div style="display:flex; align-items:center; gap:12px; padding:12px 14px; background:var(--surface); border:1px solid var(--border); border-radius:14px; box-shadow:var(--sh-sm); margin-bottom:9px;">
                <div class="bit-icon" style="flex-shrink:0;">{{ $r['emoji'] }}</div>
                <div style="flex:1; min-width:0;">
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
                        <span style="font-size:13px; font-weight:600; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $r['name'] }}</span>
                        <span style="font-size:12.5px; font-weight:700; color:{{ $bar }};">{{ $r['pct'] }}%</span>
                    </div>
                    <div style="font-size:10.5px; color:var(--text-meta); margin:3px 0 6px;">{{ $r['done'] }} of {{ $r['target'] }} · goal {{ $r['goal'] }}× / week</div>
                    <div class="progress-bar"><span class="progress-bar__fill" style="width:{{ min(100, $r['pct']) }}%; background:{{ $bar }};"></span></div>
                </div>
            </div>
        @empty
            <div style="text-align:center; padding:28px; color:var(--text-meta); font-size:12.5px; background:var(--surface); border:1px solid var(--border); border-radius:14px;">No active Bits in {{ $monthLabel }}.</div>
        @endforelse

        {{-- Calendar grid --}}
        <div style="font-size:11px; font-weight:700; color:var(--text-meta); letter-spacing:.4px; text-transform:uppercase; margin:18px 0 10px;">Activity calendar</div>
        <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:14px; box-shadow:var(--sh-sm);">
            <div style="display:grid; grid-template-columns:repeat(7,1fr); gap:6px; margin-bottom:6px;">
                @foreach ($dayLabels as $dl)
                    <div style="text-align:center; font-size:10px; font-weight:700; color:var(--text-meta);">{{ $dl }}</div>
                @endforeach
            </div>
            <div style="display:grid; grid-template-columns:repeat(7,1fr); gap:6px;">
                @foreach ($cells as $c)
                    @if ($c === null)
                        <div></div>
                    @else
                        @php
                            $bg = $c['active'] ? 'var(--grad)' : ($c['checkin'] ? 'var(--primary-lt)' : 'var(--bg-app)');
                            $fg = $c['active'] ? '#fff' : 'var(--text-meta)';
                        @endphp
                        <div style="aspect-ratio:1; border-radius:9px; background:{{ $bg }}; color:{{ $fg }}; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:600; {{ $c['today'] ? 'outline:2px solid var(--primary); outline-offset:-2px;' : '' }}">{{ $c['day'] }}</div>
                    @endif
                @endforeach
            </div>
            <div style="display:flex; gap:14px; justify-content:center; margin-top:12px; font-size:10px; color:var(--text-meta);">
                <span style="display:flex; align-items:center; gap:5px;"><span style="width:10px; height:10px; border-radius:3px; background:var(--grad);"></span> Completed a Bit</span>
                <span style="display:flex; align-items:center; gap:5px;"><span style="width:10px; height:10px; border-radius:3px; background:var(--primary-lt);"></span> Opened app</span>
            </div>
        </div>
    </div>

    {{-- Export actions --}}
    <div class="no-print" style="display:flex; gap:10px; margin-top:18px;">
        <button type="button" wire:click="export" class="btn" style="flex:1; background:var(--surface); border:1px solid var(--border-i); color:var(--text); display:flex; align-items:center; justify-content:center; gap:7px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download CSV
        </button>
        <button type="button" onclick="window.print()" class="btn btn--grad" style="flex:1; display:flex; align-items:center; justify-content:center; gap:7px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Save as PDF
        </button>
    </div>
</div>
