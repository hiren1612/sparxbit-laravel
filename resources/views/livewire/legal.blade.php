<div>
    <x-page-header :title="$title" :back="$back" />

    @php
        $card = 'background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:18px; box-shadow:var(--sh-sm);';
        $h = 'font-size:13.5px; font-weight:700; color:var(--text); margin:18px 0 6px;';
        $p = 'font-size:12.5px; line-height:1.7; color:var(--text-mid); margin:0 0 10px;';
        $muted = 'font-size:11px; color:var(--text-meta); margin:0 0 14px;';
    @endphp

    @if (($doc === 'terms' || $doc === 'privacy') && !empty($bodyHtml))
        {{-- Admin-managed content from the database --}}
        <div class="legal-body" style="{{ $card }}">
            @if ($updatedAt)<p style="{{ $muted }}">Last updated: {{ $updatedAt->format('j F Y') }}</p>@endif
            {!! $bodyHtml !!}
        </div>

    @elseif ($doc === 'terms')
        <div style="{{ $card }}">
            <p style="{{ $muted }}">Last updated: 19 October 2026</p>
            <p style="{{ $p }}">Welcome to Sparx Bit. By creating an account or using the app (the "Service"), you agree to these Terms of Service. If you do not agree, please do not use the Service.</p>
            <h2 style="{{ $h }}">1. The Service</h2>
            <p style="{{ $p }}">Sparx Bit is a habit-tracking application that lets you create habits ("Bits"), set weekly goals, and record your progress. The Service is provided on an "as is" and "as available" basis.</p>
            <h2 style="{{ $h }}">2. Your account</h2>
            <p style="{{ $p }}">You are responsible for keeping your sign-in method secure and for all activity under your account. Please tell us immediately if you suspect any unauthorised use.</p>
            <h2 style="{{ $h }}">3. Acceptable use</h2>
            <p style="{{ $p }}">You agree not to misuse the Service — including attempting to disrupt it, access it by unauthorised means, reverse-engineer it, or use it to break the law.</p>
            <h2 style="{{ $h }}">4. Your content</h2>
            <p style="{{ $p }}">Your habits and check-ins belong to you. You can export or permanently delete your data at any time from Profile.</p>
            <h2 style="{{ $h }}">5. Changes</h2>
            <p style="{{ $p }}">We may update these Terms from time to time. Continued use of the Service after changes means you accept the updated Terms.</p>
            <h2 style="{{ $h }}">6. Contact</h2>
            <p style="{{ $p }}">Questions about these Terms? Reach us at info@yourdeveloperonline.com.</p>
        </div>

    @elseif ($doc === 'privacy')
        <div style="{{ $card }}">
            <p style="{{ $muted }}">Last updated: 19 October 2026</p>
            <p style="{{ $p }}">This Privacy Policy explains what information Sparx Bit collects, how we use it, and the choices you have. We aim to collect only what we need to run the app for you.</p>
            <h2 style="{{ $h }}">Information we collect</h2>
            <p style="{{ $p }}"><b>Account information</b> — the email address (and name, if you add one) you sign in with.<br><b>Your habit data</b> — the Bits, weekly goals, check-ins, and notes you create.<br><b>Basic diagnostics</b> — limited technical data to help us fix issues.</p>
            <h2 style="{{ $h }}">How we use your information</h2>
            <p style="{{ $p }}">To provide and improve the Service — to save your habits, sync them across devices, show your progress and insights, and send the reminders you choose to enable.</p>
            <h2 style="{{ $h }}">Storage &amp; security</h2>
            <p style="{{ $p }}">Your data is stored securely on our servers. We never sell your personal data.</p>
            <h2 style="{{ $h }}">Your choices</h2>
            <p style="{{ $p }}">You can edit your profile, turn reminders on or off, export a full copy of your data, or permanently delete your account — all from Profile.</p>
            <h2 style="{{ $h }}">Contact</h2>
            <p style="{{ $p }}">Questions about privacy? Reach us at info@yourdeveloperonline.com.</p>
        </div>

    @else
        <p style="font-size:12.5px; line-height:1.7; color:var(--text-mid); margin:0 0 14px;">Sparx Bit is built with open-source software. We're grateful to the projects below and their contributors. Each is used under its respective licence.</p>
        <div style="{{ $card }} padding:6px 16px;">
            @foreach ($licenses as $i => [$name, $license])
                <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 0; {{ ! $loop->last ? 'border-bottom:1px solid var(--border-i);' : '' }}">
                    <span style="font-size:13px; font-weight:600; color:var(--text);">{{ $name }}</span>
                    <span style="font-size:11.5px; color:var(--text-meta);">{{ $license }}</span>
                </div>
            @endforeach
        </div>
    @endif
</div>
