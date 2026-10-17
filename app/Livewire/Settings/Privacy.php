<?php

namespace App\Livewire\Settings;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class Privacy extends Component
{
    public bool $autoSync = true;

    public function mount(): void
    {
        $this->autoSync = (bool) auth()->user()->auto_sync;
    }

    public function updatedAutoSync($value): void
    {
        auth()->user()->update(['auto_sync' => (bool) $value]);
    }

    public function render()
    {
        return view('livewire.settings.privacy');
    }
}
