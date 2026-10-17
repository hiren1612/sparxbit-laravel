<?php

namespace App\Livewire\Settings;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class WeekStart extends Component
{
    public string $weekStartsOn = 'monday';

    public function mount(): void
    {
        $this->weekStartsOn = auth()->user()->week_starts_on ?? 'monday';
    }

    public function select(string $value): void
    {
        $this->weekStartsOn = $value;
        auth()->user()->update(['week_starts_on' => $value]);
    }

    public function render()
    {
        return view('livewire.settings.week-start');
    }
}
