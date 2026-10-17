<div style="flex:1; display:flex; flex-direction:column; width:100%;">
    {{-- Progress dots --}}
    <div class="ob-dots" role="tablist" aria-label="Onboarding progress" style="align-self:flex-end; margin-top:-14px; margin-bottom:8px;">
        @for ($n = 1; $n <= 3; $n++)
            <span class="ob-dot{{ $n === $step ? ' ob-dot--on' : '' }}"></span>
        @endfor
    </div>

    {{-- Center content --}}
    <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center;">
        <div style="width:110px; height:110px; border-radius:24px; background:{{ $slide['bg'] }}; display:flex; align-items:center; justify-content:center; margin-bottom:24px;" aria-hidden>
            <svg width="52" height="52" viewBox="0 0 56 56" fill="none">{!! $slide['svg'] !!}</svg>
        </div>
        <h2 style="font-size:20px; font-weight:800; color:var(--text); letter-spacing:-.4px; margin-bottom:10px;">{{ $slide['title'] }}</h2>
        <p style="font-size:13px; color:var(--text-meta); line-height:1.7; padding:0 8px; max-width:300px;">{{ $slide['sub'] }}</p>
    </div>

    {{-- CTA --}}
    <div style="display:flex; flex-direction:column; align-items:center; gap:12px; padding-bottom:8px;">
        @if ($step < 3)
            <button type="button" wire:click="next" class="btn btn--grad">Next</button>
            <button type="button" wire:click="finish" class="skip-link" style="background:none; border:none; cursor:pointer;">Skip</button>
        @else
            <button type="button" wire:click="finish" class="btn btn--grad">Get Started</button>
        @endif
    </div>
</div>
