<div>
    @php
        $histStyles = [
            'done' => ['bg' => 'var(--green-lt)', 'color' => '#1A7A55', 'label' => 'Done', 'icon' => true],
            'missed' => ['bg' => 'var(--danger-lt)', 'color' => '#C53030', 'label' => 'Missed', 'icon' => false],
            'skipped' => ['bg' => 'var(--surface-tint)', 'color' => 'var(--text-meta)', 'label' => 'Skipped', 'icon' => false],
            'pending' => ['bg' => 'var(--surface-tint)', 'color' => 'var(--text-mid)', 'label' => 'Pending', 'icon' => false],
        ];
    @endphp

    {{-- Top bar --}}
    <div style="display:flex; justify-content:space-between; align-items:center; padding:28px 0 20px;">
        <a href="{{ route('bits.index') }}" wire:navigate class="hdr-back" aria-label="Go back">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--text-mid)" stroke-width="2.2" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
        </a>
        <div style="font-size:16px; font-weight:600; color:var(--text);">Bit Detail</div>
        <div style="display:flex; gap:8px;">
            <a href="{{ route('bits.edit', $bit->id) }}" wire:navigate class="hdr-back" aria-label="Edit">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-mid)" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </a>
            <button type="button" wire:click="pauseResume" class="hdr-back" aria-label="{{ $bit->status === 'paused' ? 'Resume' : 'Pause' }}"
                style="{{ $bit->status === 'paused' ? 'background:var(--primary-lt); border-color:var(--primary-mid);' : '' }}">
                @if ($bit->status === 'paused')
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="var(--primary)" stroke="none"><polygon points="6 4 20 12 6 20 6 4"/></svg>
                @else
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="var(--text-mid)" stroke="none"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                @endif
            </button>
        </div>
    </div>

    {{-- Hero --}}
    <div style="background:var(--surface); border-radius:20px; border:1px solid var(--border); padding:22px 20px 20px; margin-bottom:4px; box-shadow:var(--sh-sm);">
        <div style="display:flex; align-items:center; gap:14px; margin-bottom:{{ $bit->why ? '16px' : '0' }};">
            <div style="font-size:36px; line-height:1;">{{ $emoji }}</div>
            <div style="flex:1; min-width:0;">
                <div style="font-size:20px; font-weight:700; color:var(--text); letter-spacing:-.4px; margin-bottom:4px;">{{ $bit->title }}</div>
                <div style="display:flex; align-items:center; gap:8px; margin-top:6px; flex-wrap:wrap;">
                    @if ($categoryLabel)
                        <div style="display:inline-flex; align-items:center; gap:5px; background:var(--primary-lt); border-radius:999px; padding:3px 10px; font-size:11px; font-weight:600; color:var(--primary);">
                            <div style="width:7px; height:7px; border-radius:50%; background:var(--primary);"></div>{{ $categoryLabel }}
                        </div>
                    @endif
                    <div style="font-size:11px; color:var(--text-meta);">{{ $goal }}× / week</div>
                </div>
            </div>
            <button type="button" wire:click="toggleToday" aria-label="{{ $isDoneToday ? 'Mark not done' : 'Mark done' }}"
                style="width:44px; height:44px; border-radius:14px; border:none; cursor:pointer; flex-shrink:0; display:flex; align-items:center; justify-content:center; background:{{ $isDoneToday ? 'var(--green)' : 'var(--primary)' }}; box-shadow:0 2px 8px {{ $isDoneToday ? 'rgba(90,214,160,.2)' : 'rgba(91,141,239,.2)' }};">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
            </button>
        </div>
        @if ($bit->why)
            <div style="background:var(--primary-lt); border-radius:12px; padding:11px 14px;">
                <div style="font-size:10px; font-weight:600; color:var(--primary); letter-spacing:.4px; text-transform:uppercase; margin-bottom:3px;">Why</div>
                <div style="font-size:12.5px; color:var(--text-mid); font-style:italic; line-height:1.55;">"{{ $bit->why }}"</div>
            </div>
        @endif
    </div>

    {{-- Stats strip --}}
    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1px; background:var(--border); border-radius:0 0 18px 18px; overflow:hidden;">
        <div style="background:var(--surface); padding:14px 10px; text-align:center;">
            <div style="font-size:22px; font-weight:700; color:var(--primary); letter-spacing:-.5px;">{{ $doneThisWeek }}</div>
            <div style="font-size:10px; color:var(--text-meta); margin-top:3px;">Done this week</div>
        </div>
        <div style="background:var(--surface); padding:14px 10px; text-align:center;">
            <div style="font-size:22px; font-weight:700; color:var(--text); letter-spacing:-.5px;">{{ $totalDone }}</div>
            <div style="font-size:10px; color:var(--text-meta); margin-top:3px;">All-time done</div>
        </div>
        <div style="background:var(--surface); padding:14px 10px; text-align:center;">
            <div style="font-size:22px; font-weight:700; color:var(--green); letter-spacing:-.5px;">{{ $completion }}%</div>
            <div style="font-size:10px; color:var(--text-meta); margin-top:3px;">Weekly goal</div>
        </div>
    </div>

    {{-- This Week --}}
    <div style="margin-top:20px;">
        <div style="background:var(--surface); border-radius:18px; border:1px solid var(--border); padding:16px 18px; box-shadow:var(--sh-sm);">
            @if ($ended)
                <div style="font-size:11.5px; font-weight:600; color:var(--text-mid); background:var(--surface-tint); border-radius:10px; padding:8px 10px; margin-bottom:12px;">
                    ⏹ This Bit ended on {{ $endedLong }} — it's no longer tracked, so this week shows 0.
                </div>
            @endif
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                <span style="font-size:13px; font-weight:600; color:var(--text);">This Week</span>
                <span style="font-size:11px; color:var(--text-meta);">{{ $doneThisWeek }} of {{ $goal }} done</span>
            </div>
            <div style="display:flex; gap:6px;">
                @foreach ($weekStrip as $i => $d)
                    @php $filled = $d['status'] === 'done'; @endphp
                    <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:5px;">
                        <div style="font-size:9px; font-weight:600; color:var(--text-meta); text-transform:uppercase; letter-spacing:.4px;">{{ ['Mo','Tu','We','Th','Fr','Sa','Su'][$i] }}</div>
                        <div style="width:32px; height:32px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:600;
                            color:{{ $filled ? '#fff' : ($d['status'] === 'missed' ? 'var(--orange)' : 'var(--text-meta)') }};
                            background:{{ $filled ? 'var(--primary)' : '#F4F5FC' }}; box-shadow:{{ $filled ? '0 1px 4px rgba(91,141,239,.18)' : 'none' }};">
                            {{ $d['dayOfMonth'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Details --}}
    <div style="margin-top:20px;">
        <div style="background:var(--surface); border-radius:18px; border:1px solid var(--border); padding:16px 18px; box-shadow:var(--sh-sm);">
            @include('livewire.bits.partials.info-row', ['label' => 'Start date', 'value' => $startLong])
            @include('livewire.bits.partials.info-row', ['label' => 'End date', 'value' => $bit->end_date ? $bit->end_date->format('j M Y') : 'Unlimited'])
            @include('livewire.bits.partials.info-row', ['label' => 'Reminder', 'value' => $reminder])
            @if ($categoryLabel)
                @include('livewire.bits.partials.info-row', ['label' => 'Category', 'value' => $categoryLabel])
            @endif
            @if ($bit->description)
                @include('livewire.bits.partials.info-row', ['label' => 'Description', 'value' => $bit->description, 'last' => true])
            @endif
        </div>
    </div>

    {{-- Changes log --}}
    @if ($changes->isNotEmpty())
        <div style="margin-top:20px;">
            <div style="font-size:10px; font-weight:600; color:var(--text-meta); letter-spacing:1.4px; text-transform:uppercase; margin-bottom:9px;">Changes</div>
            <div style="background:var(--surface); border-radius:18px; border:1px solid var(--border); padding:6px 14px; box-shadow:var(--sh-sm);">
                @foreach ($changes as $c)
                    @php $lbl = $c->field === 'goal' ? 'Goal' : ($c->field === 'start' ? 'Start date' : 'End date'); @endphp
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 0; {{ ! $loop->last ? 'border-bottom:1px solid var(--border-i);' : '' }}">
                        <div>
                            <div style="font-size:12px; font-weight:600; color:var(--text);">{{ $lbl }}</div>
                            <div style="font-size:10.5px; color:var(--text-meta); margin-top:2px;">
                                <s style="opacity:.7;">{{ $c->field === 'goal' ? $c->from_val.'× / week' : $c->from_val }}</s>
                                → <strong style="color:var(--primary); font-weight:600;">{{ $c->field === 'goal' ? $c->to_val.'× / week' : $c->to_val }}</strong>
                            </div>
                        </div>
                        <div style="font-size:10.5px; color:var(--text-meta);">{{ \Illuminate\Support\Carbon::parse($c->changed_at)->format('j M') }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Recent History --}}
    <div style="margin-top:20px;">
        <div style="font-size:10px; font-weight:600; color:var(--text-meta); letter-spacing:1.4px; text-transform:uppercase; margin-bottom:9px;">Recent History</div>
        <div style="background:var(--surface); border-radius:18px; border:1px solid var(--border); padding:16px 18px; box-shadow:var(--sh-sm);">
            @foreach ($history as $r)
                @php $st = $r['inactive'] ? ['bg' => 'transparent', 'color' => 'var(--text-faint)', 'label' => 'Not active', 'icon' => false] : $histStyles[$r['status']]; @endphp
                <div @if(! $r['inactive']) wire:click="cycle('{{ $r['iso'] }}')" role="button" @endif
                    style="display:flex; align-items:center; justify-content:space-between; padding:10px 0; {{ ! $loop->last ? 'border-bottom:1px solid var(--border);' : '' }} cursor:{{ $r['inactive'] ? 'default' : 'pointer' }}; opacity:{{ $r['inactive'] ? '.6' : '1' }};">
                    <div>
                        <div style="font-size:12px; font-weight:600; color:var(--text);">{{ $r['date'] }}</div>
                        <div style="font-size:10.5px; color:var(--text-meta); margin-top:1px;">{{ $r['relative'] }}</div>
                    </div>
                    <div style="display:inline-flex; align-items:center; gap:4px; border-radius:999px; padding:3px 9px; font-size:10.5px; font-weight:600; background:{{ $st['bg'] }}; color:{{ $st['color'] }};">
                        @if ($st['icon'])<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>@endif
                        {{ $st['label'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Actions --}}
    <div style="margin-top:20px; display:flex; gap:10px;">
        <a href="{{ route('bits.edit', $bit->id) }}" wire:navigate
            style="flex:1; height:46px; border-radius:14px; background:var(--primary-lt); border:1px solid var(--primary-mid); color:var(--primary); font-size:13px; font-weight:600; display:flex; align-items:center; justify-content:center; gap:7px; text-decoration:none;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit Bit
        </a>
        <button type="button" wire:click="$set('confirmDelete', true)"
            style="flex:1; height:46px; border-radius:14px; background:var(--danger-lt); border:1px solid rgba(229,62,62,.16); color:var(--danger); font-size:13px; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:7px; font-family:inherit;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
            Delete
        </button>
    </div>

    {{-- Delete confirm --}}
    @if ($confirmDelete)
        <div class="sheet-overlay" style="align-items:center;" wire:click="$set('confirmDelete', false)">
            <div wire:click.stop style="width:calc(100% - 48px); max-width:340px; background:var(--surface); border-radius:20px; padding:24px;">
                <div style="font-size:16px; font-weight:700; color:var(--text); margin-bottom:8px;">Delete "{{ $bit->title }}"?</div>
                <div style="font-size:12.5px; color:var(--text-mid); margin-bottom:16px; line-height:1.5;">This permanently removes the Bit and all of its history. This cannot be undone.</div>
                <button type="button" class="btn" wire:click="archive" style="background:var(--surface-tint); color:var(--text); margin-bottom:8px;">Archive instead</button>
                <button type="button" class="btn" wire:click="delete" style="background:var(--danger); color:#fff; margin-bottom:8px;">Delete permanently</button>
                <button type="button" wire:click="$set('confirmDelete', false)" style="width:100%; height:44px; border-radius:12px; background:transparent; border:none; font-size:13px; font-weight:600; color:var(--text-mid); cursor:pointer; font-family:inherit;">Cancel</button>
            </div>
        </div>
    @endif
</div>
