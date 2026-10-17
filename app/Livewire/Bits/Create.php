<?php

namespace App\Livewire\Bits;

use App\Models\Bit;

class Create extends BitForm
{
    public function mount(): void
    {
        $this->startDate = now()->format('Y-m-d');
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
