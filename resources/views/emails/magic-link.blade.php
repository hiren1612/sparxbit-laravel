<!DOCTYPE html>
<html>
<body style="margin:0; padding:0; background:#eef0f7; font-family:'Segoe UI',Arial,sans-serif;">
    <div style="max-width:440px; margin:0 auto; padding:32px 20px;">
        <div style="text-align:center; margin-bottom:24px;">
            <span style="font-size:26px; font-weight:800; color:#1b2440;">✨ Sparx Bit</span>
        </div>
        <div style="background:#fff; border-radius:16px; padding:28px 24px; box-shadow:0 8px 24px -10px rgba(40,50,110,.25);">
            <h1 style="font-size:19px; color:#1b2440; margin:0 0 10px;">Sign in to Sparx Bit</h1>
            <p style="font-size:14px; color:#5b6480; line-height:1.6; margin:0 0 22px;">Tap the button below to sign in. This link works once and expires in 20 minutes. If you didn't request it, you can ignore this email.</p>
            <a href="{{ $url }}" style="display:block; text-align:center; background:linear-gradient(135deg,#5b7ff0,#3dc5bc); color:#fff; text-decoration:none; font-size:15px; font-weight:600; padding:14px; border-radius:12px;">Sign in to Sparx Bit</a>
            <p style="font-size:11px; color:#9aa2bd; line-height:1.6; margin:20px 0 0; word-break:break-all;">Or paste this link into your browser:<br>{{ $url }}</p>
        </div>
        <p style="text-align:center; font-size:11px; color:#9aa2bd; margin-top:18px;">Small habits, big change.</p>
    </div>
</body>
</html>
