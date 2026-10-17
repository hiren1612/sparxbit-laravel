<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class DeleteAccount extends Component
{
    public string $confirm = '';

    public function destroyAccount()
    {
        if (strtoupper(trim($this->confirm)) !== 'DELETE') {
            $this->addError('confirm', 'Type DELETE to confirm.');

            return;
        }

        $user = auth()->user();
        Auth::guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();
        $user->delete();

        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.settings.delete-account');
    }
}
