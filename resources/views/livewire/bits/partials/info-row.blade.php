@php($last = $last ?? false)
@php($muted = $muted ?? false)
<div style="display:flex; align-items:flex-start; justify-content:space-between; gap:16px; padding:11px 0; {{ $last ? '' : 'border-bottom:1px solid var(--border-i);' }}">
    <span style="font-size:12px; color:var(--text-meta); flex-shrink:0;">{{ $label }}</span>
    <span style="font-size:12.5px; font-weight:{{ $muted ? '400' : '600' }}; color:{{ $muted ? 'var(--text-mid)' : 'var(--text)' }}; text-align:right; line-height:1.5;">{{ $value }}</span>
</div>
