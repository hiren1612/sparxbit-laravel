<?php

namespace App\Livewire\Bits;

use App\Livewire\Dashboard;
use App\Models\Entry;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class Archive extends Component
{
    public ?string $confirmDeleteId = null;

    public function restore(string $id): void
    {
        $bit = auth()->user()->bits()->find($id);
        if ($bit) {
            $bit->update(['status' => 'active']);
        }
    }

    public function delete(string $id): void
    {
        $bit = auth()->user()->bits()->find($id);
        if ($bit && ! $bit->is_system) {
            $bit->delete();
        }
        $this->confirmDeleteId = null;
    }

    public function render()
    {
        $user = auth()->user();
        $bits = $user->bits()->where('status', 'archived')->where('is_system', false)
            ->orderByDesc('updated_at')->get();

        $archived = $bits->map(function ($b) {
            $entries = Entry::where('bit_id', $b->id)->get();
            $total = $entries->count();
            $done = $entries->where('status', 'done')->count();

            return [
                'id' => $b->id,
                'title' => $b->title,
                'emoji' => Dashboard::emoji($b->icon_key),
                'completion' => $total === 0 ? 0 : (int) round($done / $total * 100),
                'archivedAt' => $b->updated_at->format('Y-m-d'),
            ];
        });

        $confirmDelete = $this->confirmDeleteId ? $archived->firstWhere('id', $this->confirmDeleteId) : null;

        return view('livewire.bits.archive', compact('archived', 'confirmDelete'));
    }
}
