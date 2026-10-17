<?php

namespace App\Livewire\Bits;

use App\Livewire\Dashboard;
use App\Models\Entry;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class Index extends Component
{
    public string $filter = 'all';
    public string $search = '';
    public ?string $actionForId = null;
    public ?string $confirmDeleteId = null;

    public function setFilter(string $f): void
    {
        $this->filter = $f;
    }

    public function openAction(string $id): void
    {
        $this->actionForId = $id;
    }

    public function closeAction(): void
    {
        $this->actionForId = null;
    }

    public function pauseResume(string $id): void
    {
        $bit = auth()->user()->bits()->find($id);
        if ($bit && ! $bit->is_system) {
            $bit->update(['status' => $bit->status === 'paused' ? 'active' : 'paused']);
        }
        $this->actionForId = null;
    }

    public function archive(string $id): void
    {
        $bit = auth()->user()->bits()->find($id);
        if ($bit && ! $bit->is_system) {
            $bit->update(['status' => 'archived']);
        }
        $this->actionForId = null;
        $this->confirmDeleteId = null;
    }

    public function askDelete(string $id): void
    {
        $this->confirmDeleteId = $id;
        $this->actionForId = null;
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
        $today = now()->startOfDay();
        $todayISO = $today->format('Y-m-d');
        $weekStart = now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $weekDays = collect(range(0, 6))->map(fn ($i) => $weekStart->copy()->addDays($i)->format('Y-m-d'))->all();

        $bits = $user->bits()->where('is_system', false)
            ->where('status', '!=', 'archived')
            ->orderBy('created_at')->get();

        $entries = Entry::where('user_id', $user->id)->whereIn('date', $weekDays)->get();

        $display = $bits->map(function ($b) use ($entries, $todayISO) {
            $goal = max(1, $b->goal_per_week);
            $done = $entries->filter(fn ($e) => $e->bit_id === $b->id && $e->status === 'done')->count();
            $done = min($done, $goal);
            $completion = (int) round($done / $goal * 100);
            $ended = $b->end_date && $b->end_date->format('Y-m-d') < $todayISO;

            return [
                'id' => $b->id,
                'title' => $b->title,
                'emoji' => Dashboard::emoji($b->icon_key),
                'freq' => $b->goal_per_week === 7 ? 'Daily' : $b->goal_per_week.'× / week',
                'completion' => $completion,
                'done' => $done,
                'goal' => $goal,
                'status' => $b->status,
                'ended' => $ended,
                'endedLabel' => $ended ? $b->end_date->format('j M') : '',
                'why' => $b->why ?? '',
                'description' => $b->description ?? '',
            ];
        });

        $filtered = $display->filter(function ($b) {
            if ($this->filter === 'active' && ($b['status'] !== 'active' || $b['ended'])) {
                return false;
            }
            if ($this->filter === 'paused' && $b['status'] !== 'paused') {
                return false;
            }
            if ($this->filter === 'on_track' && ! ($b['status'] === 'active' && ! $b['ended'] && $b['completion'] >= 70)) {
                return false;
            }
            if ($this->filter === 'reviewing' && ! ($b['status'] === 'active' && ! $b['ended'] && $b['completion'] < 70)) {
                return false;
            }
            if ($this->search !== '') {
                $hay = strtolower($b['title'].' '.$b['why'].' '.$b['description']);
                if (! str_contains($hay, strtolower($this->search))) {
                    return false;
                }
            }

            return true;
        })->values();

        $actionFor = $this->actionForId ? $display->firstWhere('id', $this->actionForId) : null;
        $confirmDelete = $this->confirmDeleteId ? $display->firstWhere('id', $this->confirmDeleteId) : null;

        return view('livewire.bits.index', [
            'bits' => $filtered,
            'total' => $display->count(),
            'actionFor' => $actionFor,
            'confirmDelete' => $confirmDelete,
            'filters' => [
                'all' => 'All', 'active' => 'Active', 'paused' => 'Paused',
                'reviewing' => 'In progress', 'on_track' => 'On track',
            ],
        ]);
    }
}
