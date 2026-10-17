<div>
    <x-page-header title="Email address" :back="route('profile')">
        <button type="button" wire:click="save" style="height:36px; padding:0 14px; border-radius:11px; background:var(--grad); border:none; color:#fff; font-size:13px; font-weight:600; cursor:pointer; box-shadow:var(--sh-btn);">Save</button>
    </x-page-header>
    <div class="field-label">Email address</div>
    <input type="email" wire:model="email" class="field-input">
    @error('email') <div style="color:var(--danger); font-size:11px; margin-top:6px;">{{ $message }}</div> @enderror
    <p style="font-size:11.5px; color:var(--text-meta); margin-top:10px; line-height:1.5; padding:0 2px;">This is the email you use to sign in to Sparx Bit.</p>
</div>
