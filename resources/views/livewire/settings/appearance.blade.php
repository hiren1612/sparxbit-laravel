<div
    x-data
    x-on:theme-changed.window="
        const t = $event.detail.theme;
        if (t === 'system') document.documentElement.removeAttribute('data-theme');
        else document.documentElement.setAttribute('data-theme', t);
    "
>
    <x-page-header title="Appearance" :back="route('profile')" />
    <div class="sec-label" style="margin:0 2px 12px;">Theme</div>
    @foreach ($options as $key => [$label, $desc])
        <button type="button" wire:click="select('{{ $key }}')" class="setting-radio {{ $theme === $key ? 'is-active' : '' }}">
            <div>
                <div style="font-size:14px; font-weight:600; color:var(--text);">{{ $label }}</div>
                <div style="font-size:11.5px; color:var(--text-meta); margin-top:2px;">{{ $desc }}</div>
            </div>
            <span class="radio-dot"></span>
        </button>
    @endforeach
</div>
