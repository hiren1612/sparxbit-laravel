<div>
    <x-page-header title="Notifications" :back="route('profile')" />
    <div class="row-list" style="background:var(--surface); border:1px solid var(--border); border-radius:16px; overflow:hidden; box-shadow:var(--sh-sm); margin-bottom:16px;">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px; border-bottom:1px solid var(--border-i);">
            <div><div style="font-size:13.5px; font-weight:600; color:var(--text);">Push reminders</div><div style="font-size:11px; color:var(--text-meta); margin-top:2px;">Daily nudges to complete your Bits</div></div>
            <button type="button" wire:click="$toggle('push')" class="toggle{{ $push ? ' is-on' : '' }}" style="width:30px; height:17px;"></button>
        </div>
        @if ($push)
            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px; border-bottom:1px solid var(--border-i);">
                <div style="font-size:13.5px; font-weight:600; color:var(--text);">Default reminder time</div>
                <input type="time" wire:model.live="reminderTime" style="border:1px solid var(--border-i); border-radius:10px; padding:6px 10px; font-size:13px; font-weight:600; color:var(--text); background:var(--surface);">
            </div>
        @endif
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px; border-bottom:1px solid var(--border-i);">
            <div><div style="font-size:13.5px; font-weight:600; color:var(--text);">Weekly summary</div><div style="font-size:11px; color:var(--text-meta); margin-top:2px;">A recap of your week every Sunday</div></div>
            <button type="button" wire:click="$toggle('weeklySummary')" class="toggle{{ $weeklySummary ? ' is-on' : '' }}" style="width:30px; height:17px;"></button>
        </div>
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px;">
            <div><div style="font-size:13.5px; font-weight:600; color:var(--text);">Product updates</div><div style="font-size:11px; color:var(--text-meta); margin-top:2px;">News about new features</div></div>
            <button type="button" wire:click="$toggle('productUpdates')" class="toggle{{ $productUpdates ? ' is-on' : '' }}" style="width:30px; height:17px;"></button>
        </div>
    </div>
</div>
