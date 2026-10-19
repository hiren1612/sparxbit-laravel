<?php

namespace App\Livewire\Settings;

use App\Models\Entry;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class Sync extends Component
{
    public bool $autoSync = true;

    public function mount(): void
    {
        $this->autoSync = (bool) auth()->user()->auto_sync;
    }

    public function updatedAutoSync($value): void
    {
        auth()->user()->update(['auto_sync' => (bool) $value]);
    }

    // Stream a JSON export of the user's data.
    public function export()
    {
        $user = auth()->user();
        $bits = $user->bits()->get(['id', 'title', 'why', 'description', 'goal_per_week', 'start_date', 'end_date', 'icon_key', 'status', 'is_system']);
        $entries = Entry::where('user_id', $user->id)->get(['bit_id', 'date', 'status']);

        $payload = [
            'exported_at' => now()->toIso8601String(),
            'user' => ['name' => $user->name, 'email' => $user->email, 'username' => $user->username],
            'bits' => $bits,
            'entries' => $entries,
        ];

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return response()->streamDownload(function () use ($json) {
            echo $json;
        }, 'sparxbit-export-'.now()->format('Y-m-d').'.json', ['Content-Type' => 'application/json']);
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.settings.sync', [
            'bitCount' => $user->bits()->count(),
            'entryCount' => Entry::where('user_id', $user->id)->count(),
        ]);
    }
}
