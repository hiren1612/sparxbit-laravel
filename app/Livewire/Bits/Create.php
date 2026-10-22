<?php

namespace App\Livewire\Bits;

use App\Models\Bit;
use Illuminate\Support\Carbon;

class Create extends BitForm
{
    public function mount(): void
    {
        // Default to the start of the CURRENT week, so a new bit is active this
        // week and shows up today (not "upcoming" until next Monday).
        $sundayFirst = auth()->user()->week_starts_on === 'sunday';
        $this->startDate = Carbon::today()
            ->startOfWeek($sundayFirst ? Carbon::SUNDAY : Carbon::MONDAY)
            ->format('Y-m-d');
    }

    protected function startMustBeFuture(): bool
    {
        return true;
    }

    // Auto-suggest an icon from the title while the icon is still the default.
    public function updatedTitle(): void
    {
        if ($this->iconKey === 'sparkle' && ($guess = self::guessIcon($this->title))) {
            $this->iconKey = $guess;
        }
    }

    protected function mode(): string
    {
        return 'create';
    }

    public function save()
    {
        $this->validate();

        Bit::create(array_merge($this->payload(), ['user_id' => auth()->id()]));

        session()->flash('status', 'Bit created! 🎉');

        return $this->redirect(route('dashboard'), navigate: true);
    }
}
