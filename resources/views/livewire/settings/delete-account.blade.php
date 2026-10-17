<div>
    <x-page-header title="Delete account" :back="route('profile')" />
    <div style="text-align:center; padding:8px 0 20px;">
        <div style="width:64px; height:64px; border-radius:20px; background:var(--danger-lt); display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="1.8" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
        </div>
        <div style="font-size:17px; font-weight:700; color:var(--text); margin-bottom:8px;">This cannot be undone</div>
        <div style="font-size:13px; color:var(--text-meta); line-height:1.6; max-width:280px; margin:0 auto;">Deleting your account permanently removes all your Bits, check-ins, and history.</div>
    </div>
    <div class="field-label">Type DELETE to confirm</div>
    <input type="text" wire:model="confirm" class="field-input" placeholder="DELETE" style="text-align:center; letter-spacing:2px; font-weight:700;">
    @error('confirm') <div style="color:var(--danger); font-size:11px; margin-top:6px; text-align:center;">{{ $message }}</div> @enderror
    <button type="button" wire:click="destroyAccount" class="btn" style="background:var(--danger); color:#fff; margin-top:18px;">Delete my account</button>
    <a href="{{ route('profile') }}" wire:navigate style="display:block; text-align:center; margin-top:12px; font-size:13px; font-weight:600; color:var(--text-mid); text-decoration:none;">Cancel</a>
</div>
