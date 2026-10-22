<div>
    <x-page-header title="Notifications" :back="route('profile')" />

    {{-- Browser (Web Push) notifications --}}
    <div x-data="sparxBrowserPush" x-init="init()"
        style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:14px 16px; box-shadow:var(--sh-sm); margin-bottom:16px;">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
            <div style="min-width:0;">
                <div style="font-size:13.5px; font-weight:600; color:var(--text);">Browser notifications</div>
                <div style="font-size:11px; color:var(--text-meta); margin-top:2px;">Get reminder pop-ups right here in your browser</div>
            </div>
            {{-- Toggle --}}
            <button type="button" x-on:click="toggle()" x-bind:disabled="busy || ['unsupported','unconfigured','denied'].includes(state)"
                class="toggle" x-bind:class="isOn ? 'is-on' : ''" style="width:30px; height:17px; flex-shrink:0;"
                x-show="!['loading'].includes(state)"></button>
        </div>

        <template x-if="state === 'subscribed'">
            <button type="button" x-on:click="sendTest()" x-bind:disabled="busy"
                style="margin-top:12px; width:100%; height:38px; border-radius:11px; background:var(--surface-tint); border:1px solid var(--border-i); color:var(--primary); font-size:12.5px; font-weight:600; cursor:pointer; font-family:inherit;">
                Send a test notification
            </button>
        </template>

        <template x-if="state === 'denied'">
            <div style="margin-top:10px; font-size:11px; color:var(--text-meta); line-height:1.5;">Notifications are blocked for this site. Allow them in your browser's site settings, then reload.</div>
        </template>
        <template x-if="state === 'unsupported'">
            <div style="margin-top:10px; font-size:11px; color:var(--text-meta); line-height:1.5;">This browser doesn't support push notifications.</div>
        </template>
        <template x-if="state === 'unconfigured'">
            <div style="margin-top:10px; font-size:11px; color:var(--text-meta); line-height:1.5;">Push isn't configured on the server yet.</div>
        </template>

        <div x-show="message" x-text="message" x-cloak
            style="margin-top:10px; font-size:11.5px; color:var(--primary); line-height:1.5;"></div>
    </div>

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
