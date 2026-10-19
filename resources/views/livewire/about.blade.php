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
    <div class="row-list" style="background:var(--surface); border:1px solid var(--border); border-radius:16px; overflow:hidden; box-shadow:var(--sh-sm); margin-bottom:16px;">
        <x-profile-row title="Terms of Service" :href="route('terms')"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></x-profile-row>
        <x-profile-row title="Privacy Policy" :href="route('privacy')"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></x-profile-row>
        <x-profile-row title="Open-source Licences" :href="route('licenses')"><path d="M16 18l6-6-6-6M8 6l-6 6 6 6"/></x-profile-row>
    </div>
    <div style="text-align:center; font-size:11px; color:var(--text-meta); line-height:1.8;">Made with 💙 for people building better days.<br>© {{ date('Y') }} Sparx Bit</div>
</div>
