<?php

namespace App\Livewire\Settings;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class Notifications extends Component
{
    public bool $push = false;
    public string $reminderTime = '08:00';
    public bool $weeklySummary = false;
    public bool $productUpdates = false;

    public function mount(): void
    {
        $u = auth()->user();
        $this->push = (bool) $u->notifications_push_enabled;
        $this->reminderTime = $u->default_reminder_time ? substr($u->default_reminder_time, 0, 5) : '08:00';
        $this->weeklySummary = (bool) $u->notifications_weekly_summary;
        $this->productUpdates = (bool) $u->notifications_product_updates;
    }

    public function updated(): void
    {
        auth()->user()->update([
            'notifications_push_enabled' => $this->push,
            'default_reminder_time' => $this->reminderTime,
            'notifications_weekly_summary' => $this->weeklySummary,
            'notifications_product_updates' => $this->productUpdates,
        ]);
    }

    public function render()
    {
        return view('livewire.settings.notifications');
    }
}
