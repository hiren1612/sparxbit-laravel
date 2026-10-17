<div>
    @php use App\Livewire\Dashboard; @endphp

    {{-- View tabs --}}
    <div style="display:flex; gap:18px; padding:28px 0 14px;">
        @foreach (['weekly', 'monthly', 'yearly'] as $v)
            <button type="button" wire:click="setView('{{ $v }}')"
                style="background:none; border:none; padding:4px 0; font-size:16px; cursor:pointer; font-family:inherit; position:relative;
                font-weight:{{ $view === $v ? '700' : '500' }}; color:{{ $view === $v ? 'var(--text)' : 'var(--text-meta)' }};">
                {{ ucfirst($v) }}
                @if ($view === $v)<span style="position:absolute; bottom:-2px; left:0; right:0; height:3px; background:var(--primary); border-radius:2px;"></span>@endif
            </button>
        @endforeach
    </div>

    {{-- Period nav --}}
    <div style="display:flex; align-items:center; justify-content:center; gap:12px; padding:6px 0 16px;">
        <button type="button" wire:click="shiftPeriod(-1)" class="cal-nav" aria-label="Previous period">‹</button>
        <button type="button" wire:click="goToday" style="background:var(--primary-lt); color:var(--primary); border:1px solid var(--primary-mid); border-radius:999px; padding:6px 14px; font-size:13px; font-weight:600; cursor:pointer; font-family:inherit; min-width:180px; text-align:center;">{{ $rangeLabel }}</button>
        <button type="button" wire:click="shiftPeriod(1)" class="cal-nav" aria-label="Next period">›</button>
    </div>

    @if (! $hasBits)
        <div style="padding:40px 16px; text-align:center; color:var(--text-meta); font-size:13px;">No bits were active this period.</div>

    @elseif ($view === 'weekly')
        <div>
            <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:14px 12px 6px; box-shadow:var(--sh-sm);">
                <div style="display:grid; grid-template-columns:110px repeat(7,1fr) 22px; align-items:center; gap:4px; margin-bottom:10px;">
                    <div></div>
                    @foreach ($dayLabels as $l)<div style="text-align:center; font-size:11px; color:var(--text-meta); font-weight:600;">{{ $l }}</div>@endforeach
                    <div></div>
                </div>
                @foreach ($rows as $r)
                    <div style="display:grid; grid-template-columns:110px repeat(7,1fr) 22px; align-items:center; gap:4px; padding:6px 0; border-top:1px solid var(--border-i);">
                        <div style="display:flex; align-items:center; gap:6px; font-size:12.5px; font-weight:600; color:var(--text);">
                            <span style="font-size:16px;">{{ Dashboard::emoji($r['bit']->icon_key) }}</span>
                            <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $r['bit']->title }}</span>
                        </div>
                        @foreach ($r['dayStates'] as $d)
                            <button type="button" @if(! $d['isFuture'] && ! $d['outOfRange']) wire:click="toggle('{{ $r['bit']->id }}','{{ $d['iso'] }}')" @else disabled @endif
                                aria-label="{{ $r['bit']->title }} {{ $d['iso'] }}"
                                style="width:100%; aspect-ratio:1; max-width:30px; margin:0 auto; border-radius:7px;
                                background:{{ $d['outOfRange'] ? 'transparent' : ($d['done'] ? $r['pal']['filled'] : $faded) }};
                                opacity:{{ $d['outOfRange'] ? '.25' : ($d['isFuture'] ? '.35' : '1') }};
                                border:{{ $d['outOfRange'] ? '1px dashed var(--border)' : 'none' }};
                                cursor:{{ $d['isFuture'] || $d['outOfRange'] ? 'not-allowed' : 'pointer' }};"></button>
                        @endforeach
                        <div style="display:flex; align-items:center; justify-content:center;">
                            @if ($r['isPerfect'])<span style="font-size:9px; font-weight:700; color:#1E5AA8; background:rgba(91,141,239,.16); border-radius:999px; padding:2px 5px;">✓</span>@endif
                        </div>
                    </div>
                @endforeach
            </div>
            <div style="display:flex; justify-content:space-between; gap:8px; margin-top:18px;">
                @include('livewire.partials.cal-stat', ['value' => $stats['goalsMet'].' of '.$stats['goalsTotal'], 'label' => 'goals met', 'color' => '#E55353'])
                @include('livewire.partials.cal-stat', ['value' => $stats['bestDayName'] ?: '—', 'label' => $stats['bestDayCount'] > 0 ? 'best day · '.$stats['bestDayCount'].' done' : 'best day', 'color' => '#2A5FCC'])
                @include('livewire.partials.cal-stat', ['value' => (string) $stats['totalDone'], 'label' => 'check-ins done', 'color' => '#1A7575'])
                @include('livewire.partials.cal-stat', ['value' => (string) $stats['daysActive'], 'label' => 'active days', 'color' => '#B85A10'])
            </div>
        </div>

    @elseif ($view === 'monthly')
        <div style="display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:10px;">
            @foreach ($cards as $c)
                <div style="background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:18px 10px 8px; box-shadow:var(--sh-sm); position:relative; min-width:0;">
                    <div style="display:flex; align-items:center; justify-content:center; gap:4px; font-size:12px; font-weight:700; color:var(--text); margin-bottom:11px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        <span style="font-size:14px;">{{ Dashboard::emoji($c['bit']->icon_key) }}</span>
                        <span style="overflow:hidden; text-overflow:ellipsis;">{{ $c['bit']->title }}</span>
                    </div>
                    <div style="display:grid; grid-template-columns:repeat(7,1fr); gap:3px;">
                        @foreach ($c['cells'] as $cell)
                            <button type="button" @if(! $cell['isFuture'] && ! $cell['outOfRange']) wire:click="toggle('{{ $c['bit']->id }}','{{ $cell['iso'] }}')" @else disabled @endif
                                style="aspect-ratio:1; border-radius:5px; border:none; font-size:8.5px; font-weight:600; display:flex; align-items:center; justify-content:center; padding:0; min-width:0; font-family:inherit;
                                background:{{ $cell['outOfRange'] ? 'transparent' : ($cell['done'] ? $c['pal']['filled'] : $faded) }};
                                color:{{ $cell['done'] ? '#fff' : $c['pal']['text'] }};
                                opacity:{{ $cell['outOfRange'] ? '.2' : ($cell['isFuture'] ? '.35' : '1') }};
                                cursor:{{ $cell['isFuture'] || $cell['outOfRange'] ? 'not-allowed' : 'pointer' }};">{{ $cell['day'] }}</button>
                        @endforeach
                    </div>
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-top:8px; padding-top:6px; border-top:1px solid var(--border-i);">
                        <div style="display:flex; align-items:center; gap:4px;">
                            <div style="width:10px; height:10px; border-radius:50%; background:{{ $c['pal']['filled'] }};"></div>
                            <span style="font-size:11px; font-weight:700; color:{{ $c['pal']['text'] }};">{{ $c['pct'] }}%</span>
                        </div>
                        <span style="font-size:11px; font-weight:700; color:var(--text-mid);">{{ $c['doneCount'] }}d</span>
                    </div>
                    @if ($c['isPerfect'])<div style="position:absolute; top:6px; right:8px; font-size:7px; font-weight:800; letter-spacing:1px; color:{{ $c['pal']['text'] }}; opacity:.55;">PERFECT</div>@endif
                </div>
            @endforeach
        </div>

    @else
        <div style="display:flex; flex-direction:column; gap:24px;">
            @foreach ($grids as $g)
                <div>
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                        <div style="display:flex; align-items:center; gap:6px; font-size:14px; font-weight:700; color:var(--text);">
                            <span style="font-size:16px;">{{ Dashboard::emoji($g['bit']->icon_key) }}</span>{{ $g['bit']->title }}
                        </div>
                        <div style="display:flex; align-items:center; gap:12px; font-size:12px; font-weight:600; color:{{ $g['pal']['text'] }};">
                            <div style="display:flex; align-items:center; gap:4px;"><div style="width:12px; height:12px; border-radius:50%; background:{{ $g['pal']['filled'] }};"></div>{{ $g['pct'] }}%</div>
                            <div>{{ $g['doneCount'] }}D</div>
                        </div>
                    </div>
                    <div style="display:grid; grid-auto-flow:column; grid-template-rows:repeat(7,1fr); grid-auto-columns:1fr; gap:2px; height:64px; background:var(--surface); padding:10px; border-radius:12px; border:1px solid var(--border-i);">
                        @for ($k = 0; $k < $leadBlanks; $k++)<div></div>@endfor
                        @foreach ($g['cells'] as $cell)
                            <div title="{{ $cell['iso'] }}" style="border-radius:2px; background:{{ $cell['done'] ? $g['pal']['filled'] : ($cell['outOfRange'] || $cell['isFuture'] ? '#F1F3F8' : '#DCE0EA') }};"></div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
