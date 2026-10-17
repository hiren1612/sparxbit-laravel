<div>
    <x-page-header title="My Ad" :back="route('profile')">
        <button type="button" wire:click="save" style="height:36px; padding:0 14px; border-radius:11px; background:var(--grad); border:none; color:#fff; font-size:13px; font-weight:600; cursor:pointer; box-shadow:var(--sh-btn);">Save</button>
    </x-page-header>

    {{-- Preview card --}}
    <div style="background:var(--grad); border-radius:18px; padding:18px; margin-bottom:20px; color:#fff; box-shadow:0 10px 28px -12px rgba(86,96,232,.5);">
        <div style="font-size:9.5px; font-weight:700; letter-spacing:1px; text-transform:uppercase; opacity:.8; margin-bottom:8px;">Sponsored</div>
        <div style="font-size:17px; font-weight:800; letter-spacing:-.3px;">{{ $businessName ?: 'Your business name' }}</div>
        <div style="font-size:12.5px; opacity:.9; margin-top:4px; line-height:1.5;">{{ $tagline ?: 'Your tagline appears here for referral ads.' }}</div>
    </div>

    <div class="field-label">Business name</div>
    <input type="text" wire:model="businessName" class="field-input" maxlength="60" style="margin-bottom:16px;">
    <div class="field-label">Tagline</div>
    <textarea wire:model="tagline" rows="2" maxlength="120" class="field-input" style="margin-bottom:16px;"></textarea>
    <div class="field-label">Link</div>
    <input type="url" wire:model="link" class="field-input" placeholder="https://…">
    @error('link') <div style="color:var(--danger); font-size:11px; margin-top:6px;">{{ $message }}</div> @enderror
</div>
