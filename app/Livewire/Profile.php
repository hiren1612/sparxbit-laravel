<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class Profile extends Component
{
    public function render()
    {
        $u = auth()->user();
        $name = $u->name ?: explode('@', $u->email)[0];

        return view('livewire.profile', [
            'name' => $name,
            'email' => $u->email,
            'referralCode' => $u->referral_code,
            'activeBits' => $u->bits()->where('status', 'active')->count(),
        ]);
    }
}
