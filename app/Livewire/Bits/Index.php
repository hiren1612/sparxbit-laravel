<?php

namespace App\Livewire\Bits;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class Index extends Component
{
    public function render()
    {
        $bits = auth()->user()->bits()
            ->orderByDesc('is_system')->orderBy('created_at')->get();

        return view('livewire.bits.index', ['bits' => $bits]);
    }
}
