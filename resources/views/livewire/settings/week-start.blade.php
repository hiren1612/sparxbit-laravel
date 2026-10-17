<div>
    <x-page-header title="Week starts on" :back="route('profile')" />
    <div class="sec-label" style="margin:0 2px 12px;">First day of your week</div>
    @foreach (['monday' => 'Monday', 'sunday' => 'Sunday'] as $key => $label)
        <button type="button" wire:click="select('{{ $key }}')" class="setting-radio {{ $weekStartsOn === $key ? 'is-active' : '' }}">
            <div style="font-size:14px; font-weight:600; color:var(--text);">{{ $label }}</div>
            <span class="radio-dot"></span>
        </button>
    @endforeach
    <p style="font-size:11.5px; color:var(--text-meta); margin-top:8px; line-height:1.5; padding:0 4px;">This changes how Home, Calendar, and Stats group your week.</p>
</div>
