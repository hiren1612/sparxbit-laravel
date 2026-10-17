<?php

namespace App\Livewire\Bits;

use App\Models\Bit;

class Create extends BitForm
{
    public function mount(): void
    {
        $this->startDate = now()->format('Y-m-d');
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
