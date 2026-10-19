<?php

namespace App\Livewire\Settings;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class Language extends Component
{
    public string $language = 'en';

    public const OPTIONS = [
        'en' => 'English', 'es' => 'Español', 'fr' => 'Français',
        'de' => 'Deutsch', 'hi' => 'हिन्दी', 'pt' => 'Português',
    ];

    public function mount(): void
    {
        $this->language = auth()->user()->language ?? 'en';
    }

    public function select(string $code): void
    {
        if (array_key_exists($code, self::OPTIONS)) {
            $this->language = $code;
            auth()->user()->update(['language' => $code]);
        }
    }

    public function render()
    {
        return view('livewire.settings.language', ['options' => self::OPTIONS]);
    }
}
