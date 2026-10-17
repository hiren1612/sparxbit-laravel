<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class Stats extends Component
{
    public function render()
    {
        return view('livewire.stats');
    }
}
