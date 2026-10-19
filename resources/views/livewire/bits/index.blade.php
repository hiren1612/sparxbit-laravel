<div>
    <x-page-header title="All Bits" :back="route('dashboard')">
        <a href="{{ route('bits.create') }}" wire:navigate aria-label="Create new Bit"
            style="width:36px; height:36px; border-radius:11px; background:var(--primary); display:flex; align-items:center; justify-content:center; box-shadow:0 2px 8px rgba(91,141,239,.18);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </a>
    </x-page-header>

    <input type="search" class="search-input" placeholder="Search Bits" wire:model.live.debounce.250ms="search">

    <div class="chip-row">
        @foreach ($filters as $key => $label)
            <button type="button" wire:click="setFilter('{{ $key }}')"
                class="chip @if($filter === $key) is-selected @endif"
                style="height:28px; font-size:11.5px; padding:0 12px; flex-shrink:0;">{{ $label }}</button>
        @endforeach
    </div>

    <div style="display:flex; flex-direction:column; gap:10px;">
        @forelse ($bits as $b)
            @php
                $paused = $b['status'] === 'paused';
                $ended = $b['ended'];
                $barColor = $b['completion'] >= 100 ? 'var(--green)' : ($b['completion'] >= 50 ? 'var(--primary)' : 'var(--orange)');
                $pctColor = $ended ? 'var(--text-faint)' : $barColor;
            @endphp
            <div wire:key="row-{{ $b['id'] }}" class="bitrow" style="opacity:{{ $paused || $ended ? '.55' : '1' }};">
                <a href="{{ route('bits.show', $b['id']) }}" wire:navigate style="display:flex; align-items:center; gap:12px; flex:1; min-width:0; text-decoration:none;">
                    <div class="bitrow__icon" style="{{ $b['completion'] >= 100 && !$paused ? 'background:var(--green-lt);' : '' }} {{ $paused ? 'filter:grayscale(1);' : '' }}">{{ $b['emoji'] }}</div>
                    <div style="flex:1; min-width:0;">
                        <div style="display:flex; align-items:center; gap:6px; margin-bottom:4px;">
                            <div style="font-size:13.5px; font-weight:600; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $b['title'] }}</div>
                            @if ($ended)
                                <span class="badge-soft">Ended</span>
                            @elseif ($paused)
                                <span class="badge-soft">Paused</span>
                            @elseif ($b['completion'] >= 100)
                                <span class="badge-goal">GOAL HIT ✓</span>
                            @endif
                        </div>
                        <div style="font-size:10.5px; color:var(--text-meta); margin-bottom:5px;">
                            {{ $ended ? $b['freq'].' · ended '.$b['endedLabel'] : $b['freq'].' · '.$b['done'].'/'.$b['goal'].' this week' }}
                        </div>
                        <div class="bitrow__bar"><span style="width:{{ min(100, $b['completion']) }}%; background:{{ $barColor }};"></span></div>
                    </div>
                    <div style="text-align:right; min-width:38px; flex-shrink:0;">
                        <div style="font-size:13px; font-weight:700; color:{{ $pctColor }};">{{ $ended ? '—' : $b['completion'].'%' }}</div>
                    </div>
                </a>
                <button type="button" wire:click="openAction('{{ $b['id'] }}')" class="kebab" aria-label="More actions">⋯</button>
            </div>
        @empty
            @if ($total === 0)
                <div style="text-align:center; padding:48px 24px;">
                    <div style="font-size:40px; line-height:1; margin-bottom:12px;">✨</div>
                    <div style="font-size:15px; font-weight:600; color:var(--text); margin-bottom:6px;">No Bits yet</div>
                    <div style="font-size:13px; color:var(--text-meta); line-height:1.5; margin-bottom:20px;">Bits are the small habits you want to build.<br>Create your first one to get started.</div>
                    <a href="{{ route('bits.create') }}" wire:navigate class="btn btn--grad" style="display:inline-flex; align-items:center; justify-content:center; text-decoration:none; width:auto; height:40px; padding:0 18px; margin:0 auto; font-size:13px;">Create your first Bit</a>
                </div>
            @else
                <div style="text-align:center; padding:32px; color:var(--text-meta); font-size:13px;">No Bits match your filters.</div>
            @endif
        @endforelse
    </div>

    {{-- Per-row action sheet --}}
    @if ($actionFor)
        <div class="sheet-overlay" wire:click="closeAction">
            <div class="sheet" wire:click.stop>
                <div class="sheet__grip"></div>
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px; padding-bottom:14px; border-bottom:1px solid var(--border);">
                    <span style="font-size:24px;">{{ $actionFor['emoji'] }}</span>
                    <div style="font-size:14px; font-weight:600; color:var(--text);">{{ $actionFor['title'] }}</div>
                </div>
                <a href="{{ route('bits.edit', $actionFor['id']) }}" wire:navigate class="sheet__row" style="display:block; text-decoration:none;">Edit</a>
                <button type="button" class="sheet__row" wire:click="pauseResume('{{ $actionFor['id'] }}')">{{ $actionFor['status'] === 'paused' ? 'Resume' : 'Pause' }}</button>
                <button type="button" class="sheet__row" wire:click="archive('{{ $actionFor['id'] }}')">Archive</button>
                <button type="button" class="sheet__row is-danger" wire:click="askDelete('{{ $actionFor['id'] }}')">Delete</button>
                <button type="button" wire:click="closeAction"
                    style="margin-top:12px; width:100%; height:44px; border-radius:12px; background:var(--bg-app); border:1px solid var(--border-i); font-size:13px; font-weight:600; color:var(--text-mid); cursor:pointer; font-family:inherit;">Cancel</button>
            </div>
        </div>
    @endif

    {{-- Delete confirm --}}
    @if ($confirmDelete)
        <div class="sheet-overlay" style="align-items:center;" wire:click="$set('confirmDeleteId', null)">
            <div wire:click.stop style="width:calc(100% - 48px); max-width:340px; background:var(--surface); border-radius:20px; padding:24px;">
                <div style="font-size:16px; font-weight:700; color:var(--text); margin-bottom:8px;">Delete "{{ $confirmDelete['title'] }}"?</div>
                <div style="font-size:13px; color:var(--text-meta); line-height:1.5; margin-bottom:20px;">This permanently removes the Bit and all of its history. This cannot be undone.</div>
                <button type="button" class="btn" wire:click="archive('{{ $confirmDelete['id'] }}')" style="background:var(--surface-tint); color:var(--text); margin-bottom:8px;">Archive instead</button>
                <button type="button" class="btn" wire:click="delete('{{ $confirmDelete['id'] }}')" style="background:var(--danger); color:#fff; margin-bottom:8px;">Delete permanently</button>
                <button type="button" wire:click="$set('confirmDeleteId', null)" style="width:100%; height:44px; border-radius:12px; background:transparent; border:none; font-size:13px; font-weight:600; color:var(--text-mid); cursor:pointer; font-family:inherit;">Cancel</button>
            </div>
        </div>
    @endif
</div>
