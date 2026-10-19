<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.doc')]
class Legal extends Component
{
    public string $doc = 'terms';

    public function mount(string $doc = 'terms'): void
    {
        $this->doc = in_array($doc, ['terms', 'privacy', 'licenses'], true) ? $doc : 'terms';
    }

    public function render()
    {
        $titles = ['terms' => 'Terms of Service', 'privacy' => 'Privacy Policy', 'licenses' => 'Open-source Licences'];

        return view('livewire.legal', [
            'title' => $titles[$this->doc],
            'back' => auth()->check() ? route('about') : route('login'),
            'licenses' => [
                ['Laravel', 'MIT'], ['Livewire', 'MIT'], ['Filament', 'MIT'],
                ['Tailwind CSS', 'MIT'], ['Alpine.js', 'MIT'], ['PHP', 'PHP License'],
                ['DM Sans (Google Fonts)', 'OFL'],
            ],
        ]);
    }
}
