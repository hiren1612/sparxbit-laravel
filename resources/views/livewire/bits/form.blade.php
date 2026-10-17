<div style="position:relative;">
    @php
        $isValid = trim($title) !== '' && ($unlimited || $endDate !== '');
        $selectedCat = $categoryId ? $categories->firstWhere('id', $categoryId) : null;
    @endphp

    {{-- Top bar --}}
    <div style="display:flex; justify-content:space-between; align-items:center; padding:28px 0 22px;">
        <a href="{{ $mode === 'edit' ? route('dashboard') : route('bits.index') }}" wire:navigate class="hdr-back" aria-label="Go back">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--text-mid)" stroke-width="2.2" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
        </a>
        <div style="font-size:16px; font-weight:600; letter-spacing:-.2px; color:var(--text);">{{ $mode === 'edit' ? 'Edit Bit' : 'New Bit' }}</div>
        <button type="button" wire:click="save" @disabled(! $isValid)
            style="height:40px; padding:0 18px; border-radius:13px; border:none; display:flex; align-items:center; gap:6px; font-size:13px; font-weight:600; cursor:{{ $isValid ? 'pointer' : 'not-allowed' }};
            background:{{ $isValid ? 'var(--grad)' : 'var(--grad-disabled)' }}; color:{{ $isValid ? '#fff' : '#AAC0D8' }}; box-shadow:{{ $isValid ? 'var(--sh-btn)' : 'none' }};">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
            Save
        </button>
    </div>

    {{-- Title --}}
    <div class="field-label">Title</div>
    <div class="input-row" style="height:52px; border-radius:14px; margin-bottom:18px;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--text-meta)" stroke-width="2" stroke-linecap="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
        <input type="text" placeholder="e.g. Walk 30 Minutes" maxlength="50" wire:model.live="title" style="font-size:14px; font-weight:500;">
        <span style="font-size:11px; color:var(--text-meta); font-weight:500;">{{ mb_strlen($title) }}/50</span>
    </div>
    @error('title') <div style="color:var(--danger); font-size:11px; margin:-12px 0 14px;">{{ $message }}</div> @enderror

    {{-- Why --}}
    <div class="field-label">What motivates you? <span style="color:var(--text-meta); font-weight:400;">· optional</span></div>
    <textarea wire:model="why" rows="2" maxlength="300" class="field-input" placeholder="e.g. I want to feel more energised every morning…" style="margin-bottom:18px; min-height:68px;"></textarea>

    {{-- Description --}}
    <div class="field-label">Description <span style="color:var(--text-meta); font-weight:400;">· optional</span></div>
    <textarea wire:model="description" rows="2" maxlength="500" class="field-input" placeholder="e.g. A brisk 30-min walk around the park after breakfast…" style="margin-bottom:18px; min-height:68px;"></textarea>

    {{-- Goal stepper (segmented) --}}
    <div class="field-label">Goal per Week</div>
    <div style="background:var(--surface); border:1px solid var(--border-i); border-radius:16px; padding:14px; margin-bottom:18px; box-shadow:var(--sh-sm);">
        <div style="text-align:center; margin-bottom:12px;">
            <div style="font-size:24px; font-weight:800; color:var(--primary); letter-spacing:-.5px;">{{ $goalPerWeek === 7 ? 'Daily' : $goalPerWeek.'×' }}</div>
            <div style="font-size:10.5px; color:var(--text-meta);">{{ $goalPerWeek === 7 ? 'Every day' : $goalPerWeek.' days / week' }}</div>
        </div>
        <div style="display:flex; gap:5px;">
            @foreach (range(1, 7) as $n)
                <button type="button" wire:click="setGoal({{ $n }})"
                    style="flex:1; height:38px; border-radius:10px; font-size:12px; font-weight:700; cursor:pointer; font-family:inherit;
                    border:{{ $goalPerWeek === $n ? '1px solid var(--primary)' : '1px solid var(--border-i)' }};
                    background:{{ $goalPerWeek === $n ? 'var(--primary)' : 'var(--surface)' }};
                    color:{{ $goalPerWeek === $n ? '#fff' : 'var(--text-mid)' }};">{{ $n === 7 ? '7' : $n }}</button>
            @endforeach
        </div>
    </div>

    {{-- Duration --}}
    <div class="field-label">Duration</div>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:18px;">
        <div>
            <div style="font-size:10px; font-weight:600; color:var(--text-meta); text-transform:uppercase; letter-spacing:.6px; margin-bottom:5px;">Start Date</div>
            <input type="date" wire:model="startDate" class="field-input">
            <div style="font-size:10px; color:var(--text-meta); margin-top:4px;">Starts on a {{ auth()->user()->week_starts_on === 'sunday' ? 'Sunday' : 'Monday' }}.</div>
            @error('startDate') <div style="font-size:10.5px; color:var(--danger); margin-top:4px; line-height:1.4;">{{ $message }}</div> @enderror
        </div>
        <div>
            <div style="font-size:10px; font-weight:600; color:var(--text-meta); text-transform:uppercase; letter-spacing:.6px; margin-bottom:5px;">End Date</div>
            @if ($unlimited)
                <div style="font-size:13px; font-weight:600; color:var(--text); margin-top:10px;">Unlimited</div>
            @else
                <input type="date" wire:model="endDate" class="field-input">
            @endif
            <div style="display:flex; align-items:center; gap:7px; margin-top:6px;">
                <button type="button" wire:click="$toggle('unlimited')" class="toggle{{ $unlimited ? ' is-on' : '' }}" style="width:30px; height:17px;" aria-pressed="{{ $unlimited ? 'true' : 'false' }}"></button>
                <span style="font-size:11px; font-weight:{{ $unlimited ? '600' : '500' }}; color:{{ $unlimited ? 'var(--primary)' : 'var(--text-meta)' }};">{{ $unlimited ? 'Unlimited' : 'Set date' }}</span>
            </div>
            @if (! $unlimited && $endDate === '')
                <div style="font-size:10.5px; color:var(--danger); margin-top:6px; line-height:1.4;">Pick an end date or turn Unlimited back on.</div>
            @elseif (! $unlimited)
                <div style="font-size:10px; color:var(--text-meta); margin-top:4px;">Ends on a Sunday.</div>
            @endif
            @error('endDate') <div style="font-size:10.5px; color:var(--danger); margin-top:4px; line-height:1.4;">{{ $message }}</div> @enderror
        </div>
    </div>

    {{-- Category --}}
    <div class="field-label">Category <span style="color:var(--text-meta); font-weight:400;">· optional</span></div>
    <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:18px;">
        <button type="button" wire:click="$set('categoryId', null)" class="cat-chip{{ $categoryId === null ? ' is-selected' : '' }}">
            <span style="width:8px; height:8px; border-radius:50%; background:var(--text-meta);"></span>None
        </button>
        @foreach ($categories as $c)
            <button type="button" wire:click="$set('categoryId', '{{ $c->id }}')" class="cat-chip{{ $categoryId === $c->id ? ' is-selected' : '' }}">
                <span style="width:8px; height:8px; border-radius:50%; background:{{ $c->color_hex }};"></span>{{ $c->name }}
            </button>
        @endforeach
        <button type="button" wire:click="$set('catModalOpen', true)" class="cat-chip" style="border:1px dashed rgba(91,141,239,.35); color:var(--primary);">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2.4" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New
        </button>
    </div>

    {{-- Reminder --}}
    <div class="field-label">Reminder <span style="color:var(--text-meta); font-weight:400;">· optional</span></div>
    <div style="background:var(--surface); border:1px solid var(--border-i); border-radius:16px; overflow:hidden; box-shadow:var(--sh-sm); margin-bottom:18px;">
        <div style="padding:14px 16px; display:flex; align-items:center; justify-content:space-between; gap:12px; {{ $reminderOn ? 'border-bottom:1px solid var(--border-i);' : '' }}">
            <div>
                <div style="font-size:13px; font-weight:600; color:var(--text-mid);">Enable reminder</div>
                <div style="font-size:11px; color:var(--text-meta); margin-top:2px;">Get a daily nudge to complete your Bit</div>
            </div>
            <button type="button" wire:click="$toggle('reminderOn')" class="toggle{{ $reminderOn ? ' is-on' : '' }}" style="width:30px; height:17px;" aria-pressed="{{ $reminderOn ? 'true' : 'false' }}"></button>
        </div>
        @if ($reminderOn)
            <div style="padding:16px; display:flex; flex-direction:column; gap:12px;">
                <div style="background:var(--bg-app); border:1px solid var(--border-i); border-radius:12px; padding:10px 14px;">
                    <div style="font-size:9.5px; font-weight:600; color:var(--text-meta); letter-spacing:1.1px; text-transform:uppercase; margin-bottom:4px;">Time</div>
                    <input type="time" wire:model="reminderTime" style="width:100%; border:none; outline:none; background:transparent; font-size:13px; font-weight:600; color:var(--text);">
                </div>
                <div style="background:var(--bg-app); border:1px solid var(--border-i); border-radius:12px; padding:10px 14px;">
                    <div style="font-size:9.5px; font-weight:600; color:var(--text-meta); letter-spacing:1.1px; text-transform:uppercase; margin-bottom:8px;">Days</div>
                    <div style="display:flex; gap:6px; justify-content:space-between;">
                        @foreach ($dayButtons as [$d, $l])
                            @php $explicit = in_array($d, $reminderDays, true); $selected = $explicit || count($reminderDays) === 0; @endphp
                            <button type="button" wire:click="toggleDay({{ $d }})"
                                style="flex:1; height:36px; border-radius:10px; font-size:12px; font-weight:700; cursor:pointer; font-family:inherit;
                                border:{{ $explicit ? '1px solid var(--primary)' : '1px solid var(--border-i)' }};
                                background:{{ $explicit ? 'var(--primary-lt)' : ($selected ? 'transparent' : 'var(--surface-tint)') }};
                                color:{{ $explicit ? 'var(--primary)' : ($selected ? 'var(--text)' : 'var(--text-meta)') }};">{{ $l }}</button>
                        @endforeach
                    </div>
                    <div style="font-size:10.5px; color:var(--text-meta); margin-top:8px;">
                        {{ count($reminderDays) === 0 ? 'Reminds you every day. Tap days to limit to specific weekdays.' : 'Reminds you on '.count($reminderDays).' day'.(count($reminderDays) === 1 ? '' : 's').' per week.' }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Preview --}}
    <div class="field-label">Preview</div>
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:14px; display:flex; align-items:center; gap:12px; box-shadow:var(--sh-sm); margin-bottom:18px;">
        <div style="width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0; background:{{ $selectedCat ? $selectedCat->color_hex.'22' : 'var(--surface-tint)' }};">{{ $iconOptions[$iconKey] ?? '✨' }}</div>
        <div style="flex:1; min-width:0;">
            <div style="font-size:14px; font-weight:700; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ trim($title) ?: 'Your Bit title' }}</div>
            <div style="font-size:11px; color:var(--text-meta); margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ trim($why) ?: 'Weekly · '.($goalPerWeek === 7 ? 'Daily' : $goalPerWeek.'×') }}</div>
        </div>
        @if ($selectedCat)
            <span style="font-size:10px; font-weight:700; color:{{ $selectedCat->color_hex }}; background:{{ $selectedCat->color_hex }}1a; padding:3px 8px; border-radius:999px; flex-shrink:0;">{{ $selectedCat->name }}</span>
        @endif
    </div>

    {{-- Icon --}}
    <div class="field-label">Icon</div>
    <div style="display:grid; grid-template-columns:repeat(6, 1fr); gap:8px; margin-bottom:22px;">
        @foreach ($iconOptions as $key => $emoji)
            <button type="button" wire:click="setIcon('{{ $key }}')"
                style="height:46px; border-radius:12px; font-size:22px; cursor:pointer; display:flex; align-items:center; justify-content:center;
                border:{{ $iconKey === $key ? '2px solid var(--primary)' : '1px solid var(--border-i)' }};
                background:{{ $iconKey === $key ? 'var(--primary-lt)' : 'var(--surface)' }};">{{ $emoji }}</button>
        @endforeach
    </div>

    {{-- Bottom Save --}}
    <button type="button" wire:click="save" @disabled(! $isValid) class="btn btn--grad" style="{{ $isValid ? '' : 'opacity:.5; cursor:not-allowed;' }}">
        <span wire:loading.remove wire:target="save">{{ $mode === 'edit' ? 'Save Changes' : 'Create Bit' }}</span>
        <span wire:loading wire:target="save">Saving…</span>
    </button>

    {{-- New category modal --}}
    @if ($catModalOpen)
        <div class="sheet-overlay" wire:click="$set('catModalOpen', false)">
            <div class="sheet" wire:click.stop>
                <div class="sheet__grip"></div>
                <div style="font-size:15px; font-weight:700; color:var(--text); margin-bottom:14px;">New category</div>
                <input type="text" wire:model="newCatName" class="field-input" placeholder="Category name" maxlength="24" style="margin-bottom:14px;">
                @error('newCatName') <div style="color:var(--danger); font-size:11px; margin:-10px 0 12px;">{{ $message }}</div> @enderror
                <div style="display:flex; gap:10px; margin-bottom:18px;">
                    @foreach ($palette as $color)
                        <button type="button" wire:click="$set('newCatColor', '{{ $color }}')"
                            style="width:34px; height:34px; border-radius:50%; background:{{ $color }}; cursor:pointer; border:{{ $newCatColor === $color ? '3px solid var(--text)' : '3px solid transparent' }}; outline:1px solid var(--border-i);"></button>
                    @endforeach
                </div>
                <button type="button" class="btn btn--grad" wire:click="createCategory">Create category</button>
                <button type="button" wire:click="$set('catModalOpen', false)" style="width:100%; height:44px; margin-top:8px; border-radius:12px; background:transparent; border:none; font-size:13px; font-weight:600; color:var(--text-mid); cursor:pointer; font-family:inherit;">Cancel</button>
            </div>
        </div>
    @endif
</div>
