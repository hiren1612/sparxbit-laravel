<div>
    <x-page-header title="Send feedback" :back="route('profile')" />

    @if ($sent)
        <div style="background:var(--green-lt); border:1px solid rgba(90,214,160,.28); border-radius:14px; padding:14px 16px; margin-bottom:16px; font-size:13px; color:#1A7A55; font-weight:600;">
            🎉 Thanks! Your feedback has been sent.
        </div>
    @endif

    <div class="field-label">What kind of feedback?</div>
    <div style="display:flex; gap:8px; margin-bottom:18px;">
        @foreach (['general' => 'General', 'bug' => 'Bug', 'feature' => 'Feature'] as $key => $label)
            <button type="button" wire:click="$set('type', '{{ $key }}')" class="chip {{ $type === $key ? 'is-selected' : '' }}" style="height:34px; padding:0 16px;">{{ $label }}</button>
        @endforeach
    </div>

    <div class="field-label">Your message</div>
    <textarea wire:model="message" rows="6" maxlength="1000" class="field-input" placeholder="Tell us what's on your mind…"></textarea>
    @error('message') <div style="color:var(--danger); font-size:11px; margin-top:6px;">{{ $message }}</div> @enderror

    <button type="button" wire:click="submit" class="btn btn--grad" style="margin-top:16px;">Send feedback</button>
</div>
