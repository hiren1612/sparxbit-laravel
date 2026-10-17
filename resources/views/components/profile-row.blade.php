@props([
    'title' => '', 'sub' => '', 'href' => '#', 'value' => null,
    'iconBg' => 'var(--surface-tint)', 'iconFg' => 'var(--text-muted)',
    'badge' => null, 'badgeTone' => 'blue',
])
@php
    $tone = $badgeTone === 'green'
        ? ['bg' => 'var(--green-lt)', 'c' => '#1A7A55']
        : ['bg' => 'var(--primary-lt)', 'c' => 'var(--primary)'];
@endphp
<a href="{{ $href }}" wire:navigate style="display:flex; align-items:center; gap:13px; padding:14px 16px; text-decoration:none; border-bottom:1px solid var(--border-i);">
    <div style="width:36px; height:36px; border-radius:11px; background:{{ $iconBg }}; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="{{ $iconFg }}" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! $slot !!}</svg>
    </div>
    <div style="flex:1; min-width:0;">
        <div style="font-size:13.5px; font-weight:600; color:var(--text);">{{ $title }}</div>
        <div style="font-size:11px; color:var(--text-meta); margin-top:1px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $sub }}</div>
    </div>
    @if ($badge)
        <span style="font-size:10px; font-weight:700; color:{{ $tone['c'] }}; background:{{ $tone['bg'] }}; padding:3px 8px; border-radius:999px; flex-shrink:0;">{{ $badge }}</span>
    @elseif ($value !== null)
        <span style="font-size:12px; color:var(--text-meta); flex-shrink:0;">{{ $value }}</span>
    @endif
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--text-meta)" stroke-width="2.5" stroke-linecap="round" style="opacity:.6; flex-shrink:0;"><polyline points="9 18 15 12 9 6"/></svg>
</a>
