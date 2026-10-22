<?php

namespace App\Livewire;

use App\Services\NotificationFeed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class Notifications extends Component
{
    public function mount(): void
    {
        // Opening the page marks everything as seen (clears the bell dot).
        NotificationFeed::markSeen(auth()->user());
    }

    public function render()
    {
        return view('livewire.notifications', ['items' => NotificationFeed::items(auth()->user())]);
    }
}
