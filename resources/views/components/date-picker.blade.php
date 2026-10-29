@props(['field', 'value' => '', 'allowedDow' => null, 'min' => null, 'allow' => null, 'sundayFirst' => false, 'placeholder' => 'Pick a date'])
<div wire:ignore
    x-data="sparxDatePicker({ field: '{{ $field }}', allowedDow: {{ $allowedDow === null ? 'null' : (int) $allowedDow }}, minISO: '{{ $min }}', allowISO: '{{ $allow }}', sundayFirst: {{ $sundayFirst ? 'true' : 'false' }}, placeholder: '{{ $placeholder }}' })"
    style="position:relative; width:100%;">
    <button type="button" @click="open = !open" class="field-input" style="display:flex; align-items:center; justify-content:space-between; text-align:left; cursor:pointer;">
        <span x-text="display()" :style="hasValue() ? 'color:var(--text); font-weight:600;' : 'color:var(--text-meta);'"></span>
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--text-meta)" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    </button>

    <div x-show="open" x-cloak @click.outside="open = false" @keydown.escape="open = false"
        style="position:absolute; top:calc(100% + 6px); left:0; z-index:100; background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:12px; box-shadow:var(--sh-lg, 0 12px 32px -8px rgba(30,40,90,.25)); width:280px; max-width:calc(100vw - 40px);">
        {{-- Header --}}
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
            <button type="button" @click="shift(-1)" aria-label="Previous month" style="width:28px; height:28px; border-radius:8px; border:1px solid var(--border); background:var(--surface); cursor:pointer; font-size:14px;">‹</button>
            <div style="font-size:13px; font-weight:600; color:var(--text);" x-text="monthLabel"></div>
            <button type="button" @click="shift(1)" aria-label="Next month" style="width:28px; height:28px; border-radius:8px; border:1px solid var(--border); background:var(--surface); cursor:pointer; font-size:14px;">›</button>
        </div>

        {{-- Day labels --}}
        <div style="display:grid; grid-template-columns:repeat(7,1fr); gap:2px; margin-bottom:4px;">
            <template x-for="l in dayLabels" :key="l">
                <div style="text-align:center; font-size:10px; font-weight:600; color:var(--text-meta); letter-spacing:.4px; text-transform:uppercase;" x-text="l"></div>
            </template>
        </div>

        {{-- Grid --}}
        <div style="display:grid; grid-template-columns:repeat(7,1fr); gap:2px;">
            <template x-for="i in leadBlanks" :key="'b' + i"><div></div></template>
            <template x-for="d in days" :key="d.iso">
                <button type="button" @click="pick(d)" :disabled="d.disabled"
                    :style="'aspect-ratio:1; border:none; border-radius:8px; font-size:12px; font-weight:600; font-family:inherit; '
                        + (d.selected ? 'background:var(--primary); color:#fff;' : (d.disabled ? 'background:transparent; color:var(--text-faint); opacity:.35; cursor:not-allowed;' : 'background:transparent; color:var(--text); cursor:pointer;'))"
                    x-text="d.day"></button>
            </template>
        </div>

        {{-- Footer --}}
        <div style="display:flex; justify-content:space-between; margin-top:10px;">
            <button type="button" @click="clear()" style="background:transparent; border:none; color:var(--text-meta); font-size:12px; font-weight:600; cursor:pointer; font-family:inherit;">Clear</button>
            <button type="button" x-show="showToday" @click="goToday()" style="background:transparent; border:none; color:var(--primary); font-size:12px; font-weight:600; cursor:pointer; font-family:inherit;">Today</button>
        </div>
    </div>
</div>
