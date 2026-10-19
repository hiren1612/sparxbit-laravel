<div>
    <x-page-header title="Language" :back="route('profile')" />
    <div class="sec-label" style="margin:0 2px 12px;">App display language</div>
    @foreach ($options as $code => $label)
        <button type="button" wire:click="select('{{ $code }}')" class="setting-radio {{ $language === $code ? 'is-active' : '' }}">
            <div style="font-size:14px; font-weight:600; color:var(--text);">{{ $label }}</div>
            <span class="radio-dot"></span>
        </button>
    @endforeach
    <p style="font-size:11.5px; color:var(--text-meta); margin-top:8px; line-height:1.5; padding:0 4px;">More languages are on the way. Your choice is saved to your account.</p>
</div>
