<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class About extends Component
{
    public function render()
    {
        return view('livewire.about', [
            'tagline' => \App\Models\Setting::get('about.tagline', 'Small habits, big change. Sparx Bit helps you build the little daily "Bits" that compound into the person you want to become.'),
            'footer' => \App\Models\Setting::get('about.footer', 'Made with 💙 for people building better days.'),
            'version' => \App\Models\Setting::get('about.version', '1.0.0'),
        ]);
    }
}
