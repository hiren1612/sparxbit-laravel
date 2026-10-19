<!DOCTYPE html>
<html>
<body style="margin:0; padding:0; background:#eef0f7; font-family:'Segoe UI',Arial,sans-serif;">
    <div style="max-width:440px; margin:0 auto; padding:32px 20px;">
        <div style="text-align:center; margin-bottom:24px;">
            <span style="font-size:26px; font-weight:800; color:#1b2440;">✨ Sparx Bit</span>
        </div>
        <div style="background:#fff; border-radius:16px; padding:28px 24px; box-shadow:0 8px 24px -10px rgba(40,50,110,.25);">
            <h1 style="font-size:19px; color:#1b2440; margin:0 0 6px;">Time for your Bits 💪</h1>
            <p style="font-size:14px; color:#5b6480; line-height:1.6; margin:0 0 18px;">A quick nudge, {{ $user->name ?: 'there' }} — these are due today:</p>
            @foreach ($bits as $bit)
                <div style="display:flex; align-items:center; gap:10px; padding:10px 0; border-bottom:1px solid #eef0f7;">
                    <span style="font-size:20px;">{{ \App\Livewire\Dashboard::emoji($bit->icon_key) }}</span>
                    <span style="font-size:14px; font-weight:600; color:#1b2440;">{{ $bit->title }}</span>
                </div>
            @endforeach
            <a href="{{ route('dashboard') }}" style="display:block; text-align:center; margin-top:22px; background:linear-gradient(135deg,#5b7ff0,#3dc5bc); color:#fff; text-decoration:none; font-size:15px; font-weight:600; padding:14px; border-radius:12px;">Open Sparx Bit</a>
        </div>
        <p style="text-align:center; font-size:11px; color:#9aa2bd; margin-top:18px;">You're getting this because reminders are on. Turn them off in Profile → Notifications.</p>
    </div>
</body>
</html>
