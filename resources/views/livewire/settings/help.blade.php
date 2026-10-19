<div x-data="{ open: null }">
    <x-page-header title="Help & FAQ" :back="route('profile')" />

    <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:18px;">
        @foreach ($faqs as $i => [$q, $a])
            <div style="background:var(--surface); border:1px solid var(--border); border-radius:14px; box-shadow:var(--sh-sm); overflow:hidden;">
                <button type="button" x-on:click="open = open === {{ $i }} ? null : {{ $i }}"
                    style="width:100%; display:flex; align-items:center; justify-content:space-between; gap:12px; padding:15px 16px; background:transparent; border:none; cursor:pointer; font-family:inherit; text-align:left;">
                    <span style="font-size:13.5px; font-weight:600; color:var(--text);">{{ $q }}</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-meta)" stroke-width="2.4" stroke-linecap="round" x-bind:style="open === {{ $i }} ? 'transform:rotate(180deg); transition:.2s' : 'transition:.2s'"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div x-show="open === {{ $i }}" x-transition style="padding:0 16px 15px; font-size:12.5px; color:var(--text-mid); line-height:1.6;">{{ $a }}</div>
            </div>
        @endforeach
    </div>

    <a href="{{ route('feedback') }}" wire:navigate style="display:flex; align-items:center; justify-content:center; gap:8px; padding:14px; background:var(--primary-lt); border-radius:14px; text-decoration:none; color:var(--primary); font-size:13px; font-weight:600;">
        Still need help? Send feedback
    </a>
</div>
