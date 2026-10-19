<?php

namespace App\Livewire\Settings;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class Help extends Component
{
    public const FAQS = [
        ['What is a Bit?', 'A Bit is a small habit you want to build — like "Walk 30 minutes" or "Read 20 pages". You set a weekly goal and check it off as you go.'],
        ['How do weekly goals work?', 'Each Bit has a goal of how many times per week you want to do it (1–7). Your Home ring shows your overall progress toward all your goals this week.'],
        ['What is the Daily Check-In?', 'It\'s an automatic streak that counts every day you open the app and complete at least one Bit — just showing up counts.'],
        ['Can I log a day I missed?', 'Yes. Open a Bit → Recent History → tap a day to cycle its status, or use "Log past days" to backfill up to 30 days.'],
        ['How do reminders work?', 'Turn on a reminder for a Bit (or in Profile → Notifications) and pick a time and days. We\'ll email you a nudge when it\'s time.'],
        ['Is my data safe?', 'Yes. Your Bits and check-ins are stored securely. You can export a full copy any time from Profile → Sync & backup, or delete everything from Delete account.'],
    ];

    public function render()
    {
        return view('livewire.settings.help', ['faqs' => self::FAQS]);
    }
}
