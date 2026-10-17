@props(['title' => '', 'back' => null])

<div style="display:flex; justify-content:space-between; align-items:center; padding:28px 0 20px;">
    @if ($back)
        <a href="{{ $back }}" wire:navigate class="hdr-back" aria-label="Go back">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--text-mid)" stroke-width="2.2" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
        </a>
    @else
        <button type="button" onclick="history.back()" class="hdr-back" aria-label="Go back">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--text-mid)" stroke-width="2.2" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
    @endif
    <div style="font-size:16px; font-weight:600; color:var(--text); flex:1; min-width:0; text-align:center; padding:0 12px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $title }}</div>
    <div style="width:40px; flex-shrink:0; display:flex; justify-content:flex-end;">{{ $slot }}</div>
</div>
