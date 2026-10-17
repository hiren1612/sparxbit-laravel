<div>
    <x-page-header title="Privacy" :back="route('profile')" />
    <div class="row-list" style="background:var(--surface); border:1px solid var(--border); border-radius:16px; overflow:hidden; box-shadow:var(--sh-sm); margin-bottom:16px;">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px;">
            <div><div style="font-size:13.5px; font-weight:600; color:var(--text);">Auto-sync</div><div style="font-size:11px; color:var(--text-meta); margin-top:2px;">Keep your data backed up across devices</div></div>
            <button type="button" wire:click="$toggle('autoSync')" class="toggle{{ $autoSync ? ' is-on' : '' }}" style="width:30px; height:17px;"></button>
        </div>
    </div>
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:16px; box-shadow:var(--sh-sm);">
        <div style="font-size:12px; color:var(--text-mid); line-height:1.6;">
            Sparx Bit stores your habits and check-ins to power your Home, Calendar, and Insights. We never sell your data. You can export or permanently delete everything from <b style="color:var(--text);">Account</b> at any time.
        </div>
    </div>
</div>
