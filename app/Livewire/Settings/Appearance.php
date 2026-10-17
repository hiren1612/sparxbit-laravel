<?php

namespace App\Livewire\Settings;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class Appearance extends Component
{
    public string $theme = 'system';

    public function mount(): void
    {
        $this->theme = auth()->user()->theme ?? 'system';
    }

    public function select(string $theme): void
    {
        $this->theme = $theme;
        auth()->user()->update(['theme' => $theme]);
        $this->dispatch('theme-changed', theme: $theme);
    }

    public function render()
    {
        return view('livewire.settings.appearance', [
            'options' => [
                'system' => ['System', 'Follow your device setting'],
                'light' => ['Light', 'Always use the light theme'],
                'dark' => ['Dark', 'Always use the dark theme'],
            ],
        ]);
    }
}
