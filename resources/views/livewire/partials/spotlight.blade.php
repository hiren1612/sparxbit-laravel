@php
    $c = $tone === 'green'
        ? ['bg' => 'var(--green-lt)', 'border' => 'rgba(90,214,160,.2)', 'tagBg' => 'rgba(90,214,160,.16)', 'tagColor' => 'var(--green-ink)']
        : ['bg' => 'var(--orange-lt)', 'border' => 'rgba(255,154,92,.2)', 'tagBg' => 'rgba(255,154,92,.16)', 'tagColor' => 'var(--orange-ink)'];
@endphp
<div style="border-radius:18px; padding:16px; background:{{ $c['bg'] }}; border:1px solid {{ $c['border'] }}; box-shadow:var(--sh-sm);">
    <div style="display:inline-flex; align-items:center; gap:4px; border-radius:999px; padding:3px 9px; font-size:9.5px; font-weight:600; margin-bottom:10px; background:{{ $c['tagBg'] }}; color:{{ $c['tagColor'] }};">{{ $tag }}</div>
    <div style="font-size:26px; margin-bottom:8px;">{{ $emoji }}</div>
    <div style="font-size:13px; font-weight:600; color:var(--text); margin-bottom:4px; line-height:1.3;">{{ $name }}</div>
    <div style="font-size:12px; font-weight:600; color:{{ $c['tagColor'] }};">{{ $rate }} {{ $rangeLabel }}</div>
    <div style="font-size:10.5px; color:var(--text-meta); margin-top:3px; line-height:1.5;">{{ $sub }}</div>
</div>
