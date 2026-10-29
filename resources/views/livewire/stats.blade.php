<div>
    @php $SHADE = [0 => 'var(--track)', 1 => '#C5DBFB', 2 => '#93D8D3', 3 => '#63CBC4', 4 => '#3DC5BC']; @endphp

    {{-- Header + range toggle --}}
    <div style="display:flex; justify-content:space-between; align-items:center; padding:28px 0 20px;">
        <div style="font-size:20px; font-weight:700; color:var(--text); letter-spacing:-.4px;">Insights</div>
        <div style="display:flex; background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:3px; gap:2px;">
            @foreach (['week' => 'Week', 'month' => 'Month', 'all' => 'Year'] as $key => $label)
                <button type="button" wire:click="setRange('{{ $key }}')"
                    style="padding:6px 12px; border-radius:9px; font-size:11px; font-weight:600; border:none; cursor:pointer;
                    color:{{ $range === $key ? '#fff' : 'var(--text-meta)' }}; background:{{ $range === $key ? 'var(--primary)' : 'transparent' }};
                    box-shadow:{{ $range === $key ? '0 2px 5px rgba(91,141,239,.2)' : 'none' }};">{{ $label }}</button>
            @endforeach
        </div>
    </div>

    {{-- Hero score card --}}
    <div style="background:var(--grad); border-radius:22px; padding:22px 22px 20px; margin-top:18px; display:flex; align-items:center; gap:20px; box-shadow:0 18px 42px -14px rgba(86,96,232,.5), inset 0 1px 0 rgba(255,255,255,.22);">
        <div style="position:relative; flex-shrink:0; width:100px; height:100px;">
            <svg width="100" height="100" viewBox="0 0 100 100" style="transform:rotate(-90deg);">
                <circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,.28)" stroke-width="8"/>
                <circle cx="50" cy="50" r="40" fill="none" stroke="#fff" stroke-width="8" stroke-linecap="{{ $score >= 100 ? 'butt' : 'round' }}" stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $dashoffset }}" style="transition:stroke-dashoffset 1.4s cubic-bezier(.4,0,.2,1);"/>
            </svg>
            <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center;">
                <div style="font-size:{{ $score >= 100 ? '21' : '26' }}px; font-weight:800; color:#fff; letter-spacing:-1px; line-height:1;">{{ $score }}%</div>
            </div>
        </div>
        <div style="flex:1; min-width:0;">
            <div style="font-size:18px; font-weight:800; color:#fff; letter-spacing:-.3px; margin-bottom:5px;">{{ $title }}</div>
            <div style="font-size:12px; color:rgba(255,255,255,.88); line-height:1.6; margin-bottom:12px;">{{ $sub }}</div>
            <div style="display:inline-flex; align-items:center; gap:6px; border-radius:999px; padding:5px 12px; background:rgba(255,255,255,.22);">
                <span style="font-size:11px; font-weight:700; color:#fff;">{{ $delta }}</span>
            </div>
        </div>
    </div>

    {{-- Goals bar chart --}}
    @if (count($barRows) > 0)
        <div style="margin-top:18px;">
            <div class="sec-label" style="margin-bottom:10px;">Goals · {{ $rangeLabel }}</div>
            <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:16px; box-shadow:var(--sh-sm);">
                <div style="display:flex; justify-content:space-between; margin-bottom:14px;">
                    @include('livewire.partials.bar-metric', ['value' => (string) $totalGoal, 'label' => 'Total goals', 'color' => 'var(--text)'])
                    @include('livewire.partials.bar-metric', ['value' => (string) $totalDone, 'label' => 'Completed', 'color' => 'var(--green)'])
                    @include('livewire.partials.bar-metric', ['value' => $achieved.'%', 'label' => 'Achieved', 'color' => 'var(--primary)'])
                </div>
                <div style="display:flex; flex-direction:column; gap:8px;">
                    @foreach ($barRows as $r)
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div style="width:48px; font-size:10px; color:var(--text-meta); flex-shrink:0;">{{ $r['label'] }}</div>
                            <div style="flex:1; height:14px; background:var(--track); border-radius:999px; overflow:hidden;">
                                <div style="height:100%; width:{{ min(100, $r['pct']) }}%; background:{{ $r['met'] ? 'var(--green)' : 'var(--primary)' }}; border-radius:999px; transition:width 1s cubic-bezier(.4,0,.2,1);"></div>
                            </div>
                            <div style="width:44px; text-align:right; font-size:10.5px; font-weight:600; color:var(--text-mid); flex-shrink:0;">{{ $r['done'] }}/{{ $r['goal'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Spotlight --}}
    @if ($strongest && $weakest && $liveBits->count() >= 2)
        <div style="margin-top:18px;">
            <div class="sec-label" style="margin-bottom:10px;">Spotlight</div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                @include('livewire.partials.spotlight', [
                    'tone' => 'green', 'tag' => 'Strongest Bit', 'emoji' => $strongest['emoji'], 'name' => $strongest['name'],
                    'rate' => $strongest['pct'].'%', 'rangeLabel' => $rangeLabel,
                    'sub' => $strongest['rate'] == 1 ? 'Perfect run. Keep it up!' : 'Consistently completed.',
                ])
                @include('livewire.partials.spotlight', [
                    'tone' => $weakest['rate'] >= 1 ? 'green' : 'orange',
                    'tag' => $weakest['rate'] >= 1 ? 'Also strong' : 'Needs Attention',
                    'emoji' => $weakest['emoji'], 'name' => $weakest['name'], 'rate' => $weakest['pct'].'%', 'rangeLabel' => $rangeLabel,
                    'sub' => $weakest['rate'] >= 1 ? 'Every Bit is on track — nice work!' : ($weakest['rate'] == 0 ? 'No check-ins yet. Start small today.' : 'Try setting a fixed time each day.'),
                ])
            </div>
        </div>
    @endif

    {{-- Activity heatmap --}}
    <div style="margin-top:18px;">
        <div class="sec-label" style="margin-bottom:10px;">Activity · {{ $rangeLabel }}</div>
        <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:14px; box-shadow:var(--sh-sm);">
            @if ($weeks === 1)
                <div style="display:grid; grid-template-columns:repeat(7,1fr); gap:6px;">
                    @foreach ($activityCells as $c)
                        <div style="display:flex; flex-direction:column; align-items:center; gap:4px;">
                            <div style="font-size:9px; font-weight:600; color:var(--text-meta);">{{ $c['dow'] }}</div>
                            <div style="width:100%; aspect-ratio:1; background:{{ $SHADE[$c['level']] }}; border-radius:6px;"></div>
                            <div style="font-size:10px; color:var(--text-meta); font-weight:500;">{{ $c['day'] }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="display:grid; grid-template-rows:repeat(7,8px); grid-auto-flow:column; grid-auto-columns:{{ $weeks > 10 ? 'minmax(0,1fr)' : '8px' }}; gap:3px; justify-content:start;">
                    @foreach ($activityCells as $c)
                        <div style="border-radius:2px; background:{{ $c['future'] ? 'transparent' : $SHADE[$c['level']] }}; {{ $c['future'] ? 'outline:1px dashed var(--border-i); outline-offset:-1px;' : '' }}"></div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Daily check-in --}}
    <div style="margin-top:18px;">
        <div class="sec-label" style="margin-bottom:10px;">Daily Check-In</div>
        <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:16px; box-shadow:var(--sh-sm);">
            <div style="font-size:10.5px; color:var(--text-meta); line-height:1.5; margin-bottom:14px;">A <b style="color:var(--text-mid);">check-in</b> is a day you opened the app — just showing up counts. 👋</div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <div style="display:flex; align-items:baseline; gap:6px;">
                    <span style="font-size:18px;">🔥</span>
                    <span style="font-size:22px; font-weight:700; color:var(--coral-ink, #E5533D);">{{ $checkin['streak'] }}</span>
                    <span style="font-size:12px; color:var(--text-meta);">{{ $checkin['streak'] === 1 ? 'day' : 'days' }} streak</span>
                </div>
                <div style="font-size:12px; color:var(--text-meta);"><b style="color:var(--text);">{{ $checkin['total'] }}</b> {{ $checkin['total'] === 1 ? 'day' : 'days' }} total</div>
            </div>
            @if ($weeks === 1)
                <div style="display:grid; grid-template-columns:repeat(7,1fr); gap:6px;">
                    @foreach ($checkinCells as $c)
                        <div style="display:flex; flex-direction:column; align-items:center; gap:5px;">
                            <span style="font-size:9px; font-weight:600; color:var(--text-meta);">{{ $c['dow'] }}</span>
                            <div style="width:100%; max-width:30px; aspect-ratio:1; border-radius:50%; background:{{ $c['level'] ? 'var(--green)' : 'var(--track)' }}; {{ $c['isToday'] ? 'border:2px solid var(--primary);' : '' }} display:flex; align-items:center; justify-content:center;">
                                @if ($c['level'])<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>@endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="display:grid; grid-template-rows:repeat(7,9px); grid-auto-flow:column; grid-auto-columns:{{ $weeks > 10 ? 'minmax(0,1fr)' : '9px' }}; gap:3px; justify-content:start;">
                    @foreach ($checkinCells as $c)
                        <div style="border-radius:2px; background:{{ $c['future'] ? 'transparent' : ($c['level'] ? 'var(--green)' : 'var(--track)') }}; {{ $c['future'] ? 'outline:1px dashed var(--border-i); outline-offset:-1px;' : '' }}"></div>
                    @endforeach
                </div>
            @endif
            <div style="font-size:11.5px; color:var(--text-mid); margin-top:12px; text-align:center;">
                @if ($range === 'all')
                    You've opened the app on <b style="color:var(--text);">{{ $checkin['total'] }} {{ $checkin['total'] === 1 ? 'day' : 'days' }}</b> in total.
                @else
                    You opened the app <b style="color:var(--text);">{{ $checkin['inWindow'] }} of the last {{ $checkin['windowDays'] }} days</b>.
                @endif
            </div>
        </div>
    </div>

    {{-- Each bit rings --}}
    @if ($liveBits->count() > 0)
        <div style="margin-top:18px;">
            <div class="sec-label" style="margin-bottom:10px;">Each Bit · {{ $rangeLabel }}</div>
            <div style="background:var(--surface); border:1px solid var(--border); border-radius:18px; padding:18px 18px 16px; box-shadow:var(--sh-sm);">
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(84px,1fr)); gap:16px; justify-items:center;">
                    @foreach ($liveBits as $b)
                        @php
                            $met = $b['target'] > 0 && $b['done'] >= $b['target'];
                            $color = $met ? 'var(--green)' : ($b['pct'] >= 60 ? 'var(--primary)' : 'var(--orange)');
                            $R = 26; $C = 2 * M_PI * $R; $off = $C - min(1, $b['rate']) * $C;
                            $exp = $expandedBitId === $b['id'];
                        @endphp
                        <button type="button" wire:click="toggleExpand('{{ $b['id'] }}')" style="display:flex; flex-direction:column; align-items:center; gap:6px; background:none; border:none; cursor:pointer; padding:0; font-family:inherit; width:100%; min-width:0;">
                            <div style="position:relative; width:64px; height:64px;">
                                <svg width="64" height="64" viewBox="0 0 64 64" style="transform:rotate(-90deg);">
                                    <circle cx="32" cy="32" r="{{ $R }}" fill="none" stroke="#F2F3FA" style="stroke:var(--track)" stroke-width="6"/>
                                    <circle cx="32" cy="32" r="{{ $R }}" fill="none" stroke="{{ $color }}" stroke-width="6" stroke-linecap="{{ $met ? 'butt' : 'round' }}" stroke-dasharray="{{ $C }}" stroke-dashoffset="{{ $off }}" style="transition:stroke-dashoffset 1s cubic-bezier(.4,0,.2,1);"/>
                                </svg>
                                <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center;">
                                    <span style="font-size:13px; font-weight:700; color:var(--text);">{{ $b['done'] }}/{{ $b['target'] }}</span>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:3px; max-width:84px; min-width:0;">
                                <span style="font-size:13px;">{{ $b['emoji'] }}</span>
                                <span style="font-size:11px; font-weight:600; color:{{ $exp ? 'var(--primary)' : 'var(--text)' }}; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $b['name'] }}</span>
                            </div>
                            <span style="font-size:11px; font-weight:700; color:{{ $color }};">{{ $b['pct'] }}%{{ $met ? ' ✓' : '' }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
