<div>
    <x-page-header title="Sync & backup" :back="route('profile')" />

    <div class="row-list" style="background:var(--surface); border:1px solid var(--border); border-radius:16px; overflow:hidden; box-shadow:var(--sh-sm); margin-bottom:16px;">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px;">
            <div><div style="font-size:13.5px; font-weight:600; color:var(--text);">Auto-sync</div><div style="font-size:11px; color:var(--text-meta); margin-top:2px;">Keep your data backed up across devices</div></div>
            <button type="button" wire:click="$toggle('autoSync')" class="toggle{{ $autoSync ? ' is-on' : '' }}" style="width:30px; height:17px;"></button>
        </div>
    </div>

    <div class="sec-label" style="margin:0 2px 10px;">Your data</div>
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:16px; box-shadow:var(--sh-sm); margin-bottom:16px;">
        <div style="display:flex; gap:24px; margin-bottom:14px;">
            <div><div style="font-size:22px; font-weight:800; color:var(--text);">{{ $bitCount }}</div><div style="font-size:11px; color:var(--text-meta);">Bits</div></div>
            <div><div style="font-size:22px; font-weight:800; color:var(--text);">{{ $entryCount }}</div><div style="font-size:11px; color:var(--text-meta);">Check-ins</div></div>
        </div>
        <button type="button" wire:click="export" class="btn" style="background:var(--surface-tint); color:var(--text); display:flex; align-items:center; justify-content:center; gap:8px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export my data (JSON)
        </button>
    </div>

    <p style="font-size:11.5px; color:var(--text-meta); line-height:1.6; padding:0 4px;">Your Bits and check-ins are stored securely on Sparx Bit's servers. Export gives you a full copy of your data any time.</p>
</div>
