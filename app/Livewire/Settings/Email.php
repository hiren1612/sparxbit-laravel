<?php

namespace App\Livewire\Settings;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class Email extends Component
{
    public string $email = '';

    public function mount(): void
    {
        $this->email = auth()->user()->email;
    }

    public function save()
    {
        $data = $this->validate([
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore(auth()->id())],
        ]);

        auth()->user()->update(['email' => $data['email']]);
        session()->flash('status', 'Email updated ✓');

        return $this->redirect(route('profile'), navigate: true);
    }

    public function render()
    {
        return view('livewire.settings.email');
    }
}
