<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class Calendar extends Component
{
    public function render()
    {
        return view('livewire.calendar');
    }
}
