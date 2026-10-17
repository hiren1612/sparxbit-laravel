<?php

namespace App\Livewire;

use App\Models\Entry;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class Dashboard extends Component
{
    // Toggle today's "done" status for a bit.
    public function toggle(string $bitId): void
    {
        $user = auth()->user();
        $bit = $user->bits()->find($bitId);
        if (! $bit) {
            return;
        }
        $today = now()->format('Y-m-d');
        $entry = Entry::where('bit_id', $bitId)->whereDate('date', $today)->first();

        if ($entry && $entry->status === 'done') {
            $entry->delete();
        } else {
            Entry::updateOrCreate(
                ['bit_id' => $bitId, 'date' => $today],
                ['user_id' => $user->id, 'status' => 'done', 'source' => 'manual', 'completed_at' => now()],
            );
        }
    }

    public function render()
    {
        $user = auth()->user();
        $today = now()->format('Y-m-d');

        $start = now()->startOfWeek($user->week_starts_on === 'sunday' ? Carbon::SUNDAY : Carbon::MONDAY);
        $weekDates = collect(range(0, 6))->map(fn ($i) => $start->copy()->addDays($i)->format('Y-m-d'));

        $bits = $user->bits()->where('status', 'active')
            ->orderByDesc('is_system')->orderBy('created_at')->get();

        $entries = Entry::where('user_id', $user->id)
            ->whereIn('date', $weekDates->all())->get();

        $rows = $bits->map(function ($b) use ($entries, $today) {
            $doneToday = $entries->first(fn ($e) => $e->bit_id === $b->id
                && $e->date->format('Y-m-d') === $today && $e->status === 'done') !== null;
            $doneThisWeek = $entries->filter(fn ($e) => $e->bit_id === $b->id && $e->status === 'done')->count();

            return [
                'id' => $b->id,
                'title' => $b->title,
                'doneToday' => $doneToday,
                'doneThisWeek' => min($doneThisWeek, $b->goal_per_week),
                'goal' => $b->goal_per_week,
            ];
        });

        // Weekly % excludes the system Daily Check-In bit.
        $realBits = $bits->where('is_system', false)->pluck('id');
        $totalGoal = $rows->whereIn('id', $realBits)->sum('goal');
        $totalDone = $rows->whereIn('id', $realBits)->sum('doneThisWeek');
        $weekPct = $totalGoal > 0 ? (int) round($totalDone / $totalGoal * 100) : 0;

        $first = $user->name ? explode(' ', $user->name)[0] : explode('@', $user->email)[0];

        return view('livewire.dashboard', [
            'rows' => $rows,
            'weekPct' => $weekPct,
            'displayName' => $first,
        ]);
    }
}
