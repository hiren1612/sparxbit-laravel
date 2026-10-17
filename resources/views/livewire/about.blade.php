<div>
    <x-page-header title="About" :back="route('profile')" />
    <div style="text-align:center; padding:12px 0 24px;">
        <div style="width:72px; height:72px; border-radius:22px; background:var(--grad); display:flex; align-items:center; justify-content:center; font-size:36px; margin:0 auto 16px; box-shadow:0 8px 24px -8px rgba(91,141,239,.5);">✨</div>
        <div style="font-size:20px; font-weight:800; color:var(--text); letter-spacing:-.4px;">Sparx Bit</div>
        <div style="font-size:12.5px; color:var(--text-meta); margin-top:4px;">Version 1.0.0</div>
    </div>
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:16px; box-shadow:var(--sh-sm); margin-bottom:16px;">
        <div style="font-size:13px; color:var(--text-mid); line-height:1.7;">Small habits, big change. Sparx Bit helps you build the little daily "Bits" that compound into the person you want to become.</div>
    </div>
    <div style="text-align:center; font-size:11px; color:var(--text-meta); line-height:1.8;">Made with 💙 for people building better days.<br>© {{ date('Y') }} Sparx Bit</div>
</div>
