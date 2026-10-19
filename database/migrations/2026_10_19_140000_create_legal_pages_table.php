<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // terms | privacy
            $table->string('title');
            $table->longText('body'); // HTML (edited in the admin rich editor)
            $table->timestamps();
        });

        $now = now();
        DB::table('legal_pages')->insert([
            [
                'slug' => 'terms',
                'title' => 'Terms of Service',
                'body' => $this->termsHtml(),
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'slug' => 'privacy',
                'title' => 'Privacy Policy',
                'body' => $this->privacyHtml(),
                'created_at' => $now, 'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_pages');
    }

    private function termsHtml(): string
    {
        return <<<'HTML'
<p>Welcome to Sparx Bit. By creating an account or using the app (the "Service"), you agree to these Terms of Service. If you do not agree, please do not use the Service.</p>
<h3>1. The Service</h3>
<p>Sparx Bit is a habit-tracking application that lets you create habits ("Bits"), set weekly goals, and record your progress. The Service is provided on an "as is" and "as available" basis.</p>
<h3>2. Your account</h3>
<p>You are responsible for keeping your sign-in method secure and for all activity under your account. Please tell us immediately if you suspect any unauthorised use.</p>
<h3>3. Acceptable use</h3>
<p>You agree not to misuse the Service — including attempting to disrupt it, access it by unauthorised means, reverse-engineer it, or use it to break the law.</p>
<h3>4. Your content</h3>
<p>Your habits and check-ins belong to you. You can export or permanently delete your data at any time from Profile.</p>
<h3>5. Changes</h3>
<p>We may update these Terms from time to time. Continued use of the Service after changes means you accept the updated Terms.</p>
<h3>6. Contact</h3>
<p>Questions about these Terms? Reach us at info@yourdeveloperonline.com.</p>
HTML;
    }

    private function privacyHtml(): string
    {
        return <<<'HTML'
<p>This Privacy Policy explains what information Sparx Bit collects, how we use it, and the choices you have. We aim to collect only what we need to run the app for you.</p>
<h3>Information we collect</h3>
<p><strong>Account information</strong> — the email address (and name, if you add one) you sign in with.<br><strong>Your habit data</strong> — the Bits, weekly goals, check-ins, and notes you create.<br><strong>Basic diagnostics</strong> — limited technical data to help us fix issues.</p>
<h3>How we use your information</h3>
<p>To provide and improve the Service — to save your habits, sync them across devices, show your progress and insights, and send the reminders you choose to enable.</p>
<h3>Storage &amp; security</h3>
<p>Your data is stored securely on our servers. We never sell your personal data.</p>
<h3>Your choices</h3>
<p>You can edit your profile, turn reminders on or off, export a full copy of your data, or permanently delete your account — all from Profile.</p>
<h3>Contact</h3>
<p>Questions about privacy? Reach us at info@yourdeveloperonline.com.</p>
HTML;
    }
};
