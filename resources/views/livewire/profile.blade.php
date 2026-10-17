<div>
    <div class="greeting-name" style="font-size:19px; margin-bottom:18px;">Profile</div>

    <div class="card" style="display:flex; align-items:center; gap:14px; margin-bottom:16px;">
        <div class="avatar-tile" style="width:56px; height:56px; border-radius:18px; font-size:22px;">
            {{ strtoupper(substr($name, 0, 1)) }}
        </div>
        <div style="flex:1; min-width:0;">
            <div class="bit-title" style="font-size:16px;">{{ $name }}</div>
            <div class="bit-meta">{{ $email }}</div>
        </div>
    </div>

    <div class="card" style="display:flex; margin-bottom:16px; text-align:center;">
        <div style="flex:1;">
            <div style="font-size:22px; font-weight:700; color:var(--primary);">{{ $activeBits }}</div>
            <div class="sec-label" style="margin-top:2px;">Active Bits</div>
        </div>
        <div style="width:1px; background:var(--divider);"></div>
        <div style="flex:1;">
            <div style="font-size:14px; font-weight:700; color:var(--text);">{{ $referralCode }}</div>
            <div class="sec-label" style="margin-top:2px;">Your code</div>
        </div>
    </div>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn--secondary" style="color:var(--danger);">Sign out</button>
    </form>
</div>
