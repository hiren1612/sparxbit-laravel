<?php

namespace App\Livewire\Bits;

use App\Models\Bit;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class Create extends Component
{
    public string $title = '';
    public string $why = '';
    public int $goalPerWeek = 7;
    public string $startDate = '';
    public ?string $categoryId = null;

    public function mount(): void
    {
        $this->startDate = now()->format('Y-m-d');
    }

    public function save()
    {
        $data = $this->validate([
            'title' => 'required|string|max:50',
            'why' => 'nullable|string|max:300',
            'goalPerWeek' => 'required|integer|min:1|max:7',
            'startDate' => 'required|date',
            'categoryId' => 'nullable|string',
        ]);

        $categoryId = $data['categoryId'] ?: null;
        if ($categoryId && ! auth()->user()->categories()->where('id', $categoryId)->exists()) {
            $categoryId = null;
        }

        Bit::create([
            'user_id' => auth()->id(),
            'title' => $data['title'],
            'why' => $data['why'] ?: null,
            'goal_per_week' => $data['goalPerWeek'],
            'start_date' => $data['startDate'],
            'category_id' => $categoryId,
            'reminder_days' => [],
        ]);

        session()->flash('status', 'Bit created! 🎉');

        return $this->redirect(route('dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.bits.create', [
            'categories' => auth()->user()->categories()->orderBy('name')->get(),
        ]);
    }
}
