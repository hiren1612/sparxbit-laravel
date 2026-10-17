<?php

namespace App\Livewire;

use App\Services\UserProvisioning;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class Refer extends Component
{
    public string $code = '';

    public function mount(): void
    {
        $user = auth()->user();
        if (! $user->referral_code) {
            $user->update(['referral_code' => UserProvisioning::genReferralCode()]);
        }
        $this->code = strtoupper($user->referral_code);
    }

    public function render()
    {
        $invited = auth()->user()->referredUsers()->count();

        return view('livewire.refer', [
            'invited' => $invited,
            'link' => url('/?ref='.strtolower($this->code)),
        ]);
    }
}
