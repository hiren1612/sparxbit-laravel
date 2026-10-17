<div x-data="{ copied: false }">
    <x-page-header title="Refer & Earn" :back="route('profile')" />

    <div style="background:var(--grad); border-radius:22px; padding:28px 22px; text-align:center; color:#fff; margin-bottom:20px; box-shadow:0 18px 42px -14px rgba(86,96,232,.5);">
        <div style="font-size:44px; margin-bottom:10px;">🎁</div>
        <div style="font-size:19px; font-weight:800; letter-spacing:-.3px; margin-bottom:6px;">Invite friends, unlock rewards</div>
        <div style="font-size:12.5px; opacity:.9; line-height:1.6;">Share your code. When a friend joins, you both move closer to a free premium month.</div>
    </div>

    <div class="sec-label" style="margin:0 2px 10px;">Your referral code</div>
    <div style="display:flex; gap:10px; margin-bottom:20px;">
        <div style="flex:1; background:var(--surface); border:2px dashed var(--primary-mid); border-radius:14px; padding:16px; text-align:center; font-size:22px; font-weight:800; letter-spacing:3px; color:var(--primary);">{{ $code }}</div>
        <button type="button"
            x-on:click="navigator.clipboard.writeText('{{ $code }}').then(() => { copied = true; setTimeout(() => copied = false, 1500); })"
            style="width:56px; border-radius:14px; background:var(--primary); border:none; color:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center;">
            <svg x-show="!copied" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            <svg x-show="copied" x-cloak width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
        </button>
    </div>

    <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:16px; box-shadow:var(--sh-sm); display:flex; align-items:center; justify-content:space-between;">
        <div><div style="font-size:12px; color:var(--text-meta);">Friends invited</div><div style="font-size:24px; font-weight:800; color:var(--text);">{{ $invited }}</div></div>
        <button type="button"
            x-on:click="navigator.clipboard.writeText('Join me on Sparx Bit! {{ $link }}').then(() => { copied = true; setTimeout(() => copied = false, 1500); })"
            class="btn btn--grad" style="width:auto; padding:0 20px;">Share link</button>
    </div>
</div>
