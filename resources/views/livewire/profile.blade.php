<div>
    {{-- Header --}}
    <div style="display:flex; justify-content:space-between; align-items:center; padding:28px 0 20px;">
        <div style="font-size:20px; font-weight:700; color:var(--text); letter-spacing:-.4px;">Profile</div>
        <a href="{{ route('profile.edit') }}" wire:navigate class="hdr-back" aria-label="Edit profile">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="var(--text-mid)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        </a>
    </div>

    {{-- Avatar card --}}
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:18px; padding:24px 20px 20px; display:flex; flex-direction:column; align-items:center; box-shadow:var(--sh-sm); margin-bottom:16px;">
        <div style="position:relative; margin-bottom:14px;">
            <div style="width:80px; height:80px; border-radius:24px; overflow:hidden; background:var(--grad); display:flex; align-items:center; justify-content:center; font-size:28px; font-weight:700; color:#fff; letter-spacing:-1px; box-shadow:0 4px 16px rgba(91,141,239,.2);">
                @if ($avatarUrl && str_starts_with($avatarUrl, 'emoji:'))
                    <span style="font-size:46px;">{{ substr($avatarUrl, 6) }}</span>
                @elseif ($avatarUrl)
                    <img src="{{ $avatarUrl }}" alt="avatar" style="width:100%; height:100%; object-fit:cover;">
                @else
                    {{ strtoupper(substr($displayName, 0, 1)) }}
                @endif
            </div>
        </div>
        <div style="font-size:20px; font-weight:700; color:var(--text); letter-spacing:-.4px; margin-bottom:3px; max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $displayName }}</div>
        <div style="font-size:12px; color:var(--text-meta); margin-bottom:14px;">{{ $email }}{{ $username ? ' · @'.$username : '' }}</div>
        @if ($bio)<div style="font-size:12px; color:var(--text-mid); text-align:center; margin-bottom:16px; line-height:1.55; max-width:260px;">{{ $bio }}</div>@endif
        <div style="display:flex; width:100%; border-top:1px solid var(--border); padding-top:16px;">
            <div style="flex:1; text-align:center;"><div style="font-size:19px; font-weight:700; color:var(--primary);">{{ $activeBits }}</div><div style="font-size:10.5px; color:var(--text-meta); margin-top:2px;">Active Bits</div></div>
            <div style="width:1px; background:var(--border);"></div>
            <div style="flex:1; text-align:center;"><div style="font-size:19px; font-weight:700; color:var(--green);">{{ $daysTracked }}</div><div style="font-size:10.5px; color:var(--text-meta); margin-top:2px;">Days tracked</div></div>
            <div style="width:1px; background:var(--border);"></div>
            <div style="flex:1; text-align:center;"><div style="font-size:19px; font-weight:700; color:var(--text);">{{ $thisWeek }}%</div><div style="font-size:10.5px; color:var(--text-meta); margin-top:2px;">This week</div></div>
        </div>
    </div>

    {{-- Refer & Earn --}}
    <a href="{{ route('refer') }}" wire:navigate style="width:100%; background:var(--surface); border:1px solid var(--border); border-radius:18px; padding:16px 18px; display:flex; align-items:center; gap:14px; box-shadow:var(--sh-sm); margin-bottom:16px; text-decoration:none;">
        <div style="width:44px; height:44px; border-radius:14px; background:var(--primary-lt); display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:22px;">🎁</div>
        <div style="flex:1;">
            <div style="font-size:13px; font-weight:600; color:var(--text); margin-bottom:3px;">Refer &amp; Earn</div>
            <div style="font-size:11.5px; color:var(--text-meta); line-height:1.5;">Invite friends and unlock rewards →</div>
        </div>
        <div style="width:40px; height:40px; border-radius:13px; background:var(--primary); display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow:0 2px 8px rgba(91,141,239,.18);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
    </a>

    {{-- My Ad --}}
    <div class="row-list" style="background:var(--surface); border:1px solid var(--border); border-radius:18px; overflow:hidden; box-shadow:var(--sh-sm); margin-bottom:16px;">
        <x-profile-row title="My Ad" sub="Your business name & tagline for referral ads" :href="route('profile.myad')" iconBg="var(--primary-lt)" iconFg="var(--primary)"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></x-profile-row>
    </div>

    {{-- My Bits --}}
    <div class="sec-label" style="margin:0 2px 8px;">My Bits</div>
    <div class="row-list" style="background:var(--surface); border:1px solid var(--border); border-radius:18px; overflow:hidden; box-shadow:var(--sh-sm); margin-bottom:16px;">
        <x-profile-row title="All Bits" sub="Manage your active and paused Bits" :href="route('bits.index')" iconBg="var(--primary-lt)" iconFg="var(--primary)" :badge="$activeBits.' active'"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></x-profile-row>
        <x-profile-row title="Categories" sub="Organise Bits into custom groups" :href="route('categories')" iconBg="var(--green-lt)" iconFg="#1A7A55"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></x-profile-row>
        <x-profile-row title="Archive" sub="Completed and removed Bits" :href="route('bits.archive')"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5" rx="1"/><line x1="10" y1="12" x2="14" y2="12"/></x-profile-row>
    </div>

    {{-- Preferences --}}
    <div class="sec-label" style="margin:0 2px 8px;">Preferences</div>
    <div class="row-list" style="background:var(--surface); border:1px solid var(--border); border-radius:18px; overflow:hidden; box-shadow:var(--sh-sm); margin-bottom:16px;">
        <x-profile-row title="Notifications" sub="Reminders, summaries, milestones" :href="route('profile.notifications')" iconBg="var(--orange-lt)" iconFg="#B85A10"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></x-profile-row>
        <x-profile-row title="Appearance" sub="Light · Dark · System" :href="route('profile.appearance')" :value="$themeLabel"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></x-profile-row>
        <x-profile-row title="Week starts on" sub="First day of your week" :href="route('profile.weekstart')" :value="$weekStartLabel"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></x-profile-row>
    </div>

    {{-- Account --}}
    <div class="sec-label" style="margin:0 2px 8px;">Account</div>
    <div class="row-list" style="background:var(--surface); border:1px solid var(--border); border-radius:18px; overflow:hidden; box-shadow:var(--sh-sm); margin-bottom:16px;">
        <x-profile-row title="Edit profile" sub="Name, bio, avatar" :href="route('profile.edit')" iconBg="var(--primary-lt)" iconFg="var(--primary)"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></x-profile-row>
        <x-profile-row title="Email address" :sub="$email" :href="route('profile.email')"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 7l10 7 10-7"/></x-profile-row>
        <x-profile-row title="Privacy" sub="Data, visibility, permissions" :href="route('profile.privacy')"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></x-profile-row>
    </div>

    {{-- Support --}}
    <div class="sec-label" style="margin:0 2px 8px;">Support</div>
    <div class="row-list" style="background:var(--surface); border:1px solid var(--border); border-radius:18px; overflow:hidden; box-shadow:var(--sh-sm); margin-bottom:16px;">
        <x-profile-row title="Send feedback" sub="Help us improve Sparx Bit" :href="route('feedback')" iconBg="var(--primary-lt)" iconFg="var(--primary)"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></x-profile-row>
        <x-profile-row title="About" sub="Version 1.0.0" :href="route('about')" badge="Latest" badgeTone="green"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></x-profile-row>
    </div>

    {{-- Sign out --}}
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:18px; overflow:hidden; box-shadow:var(--sh-sm); margin-bottom:12px;">
        <button type="button" wire:click="$set('signOutOpen', true)" style="width:100%; display:flex; align-items:center; gap:13px; padding:14px 16px; background:transparent; border:none; cursor:pointer; font-family:inherit;">
            <div style="width:36px; height:36px; border-radius:11px; background:var(--bg-app); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="var(--text-mid)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </div>
            <div style="flex:1; text-align:left;">
                <div style="font-size:13.5px; font-weight:600; color:var(--text);">Sign out</div>
                <div style="font-size:11px; color:var(--text-meta);">End this session on this device</div>
            </div>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--text-meta)" stroke-width="2.5" stroke-linecap="round" style="opacity:.6;"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
    </div>

    {{-- Delete account --}}
    <div style="background:var(--danger-lt); border:1px solid rgba(229,62,62,.1); border-radius:18px; overflow:hidden; box-shadow:var(--sh-sm); margin-bottom:20px;">
        <a href="{{ route('profile.delete') }}" wire:navigate style="width:100%; display:flex; align-items:center; gap:13px; padding:14px 16px; text-decoration:none;">
            <div style="width:36px; height:36px; border-radius:11px; background:var(--danger-lt); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="1.8" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
            </div>
            <div style="flex:1; text-align:left;">
                <div style="font-size:13.5px; font-weight:600; color:var(--danger);">Delete account</div>
                <div style="font-size:11px; color:rgba(229,62,62,.5);">Permanently remove all data</div>
            </div>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2.5" stroke-linecap="round" style="opacity:.4;"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
    </div>

    {{-- Sign out confirm --}}
    @if ($signOutOpen)
        <div class="sheet-overlay" wire:click="$set('signOutOpen', false)">
            <div class="sheet" wire:click.stop style="padding:24px 22px 36px;">
                <div class="sheet__grip"></div>
                <h2 style="font-size:17px; font-weight:700; color:var(--text); text-align:center; margin-bottom:8px;">Sign out of Sparx Bit?</h2>
                <p style="font-size:13px; color:var(--text-meta); text-align:center; line-height:1.6; margin-bottom:24px;">You can sign in again any time with the same email.</p>
                <div style="display:flex; gap:10px;">
                    <button type="button" class="btn btn--secondary" wire:click="$set('signOutOpen', false)" style="flex:1;">Cancel</button>
                    <button type="button" wire:click="logout" style="flex:2; height:48px; border-radius:14px; background:var(--text); border:none; font-size:14px; font-weight:600; color:var(--surface); cursor:pointer; font-family:inherit;">Sign out</button>
                </div>
            </div>
        </div>
    @endif
</div>
