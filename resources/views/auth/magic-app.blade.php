<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Opening Sparx Bit…</title>
    <style>
        :root { --bg:#f5f6fc; --card:#fff; --text:#16203a; --meta:#7c84a0; --brand:#5b7ff0; }
        @media (prefers-color-scheme: dark) { :root { --bg:#101728; --card:#182034; --text:#eaeef9; --meta:#8891ad; --brand:#74a6f7; } }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px;
               background:var(--bg); color:var(--text); font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif; }
        .card { width:100%; max-width:360px; background:var(--card); border-radius:20px; padding:32px 24px; text-align:center;
                box-shadow:0 14px 34px -16px rgba(26,33,64,.25); }
        .logo { width:64px; height:64px; margin:0 auto 16px; border-radius:18px; font-size:30px; display:flex; align-items:center; justify-content:center;
                background:linear-gradient(135deg,#5b7ff0 0%,#7c6ff2 48%,#34d1b4 100%); }
        h1 { font-size:20px; margin:0 0 8px; }
        p { font-size:14px; line-height:1.55; color:var(--meta); margin:0 0 22px; }
        a.btn { display:block; padding:14px; border-radius:12px; color:#fff; font-weight:700; font-size:15px; text-decoration:none;
                background:linear-gradient(135deg,#5b7ff0 0%,#7c6ff2 48%,#34d1b4 100%); }
        a.btn:focus-visible { outline:3px solid var(--brand); outline-offset:3px; }
        small { display:block; margin-top:18px; font-size:12px; color:var(--meta); line-height:1.5; }
    </style>
</head>
<body>
    <main class="card">
        <div class="logo" aria-hidden="true">✨</div>
        <h1>Opening Sparx Bit…</h1>
        <p>If the app doesn't open by itself, tap the button below.</p>
        <a class="btn" id="open" href="sparxbit://magic?token={{ urlencode($token) }}">Open Sparx Bit</a>
        <small>On a computer? Open this email on the phone where Sparx Bit is installed. The link works once and expires in 20 minutes.</small>
    </main>
    <script>
        // Try to hand the link straight to the app.
        window.location.href = document.getElementById('open').getAttribute('href');
    </script>
</body>
</html>
