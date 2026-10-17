<?php

namespace App\Livewire;

use App\Models\Entry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class Profile extends Component
{
    public bool $signOutOpen = false;

    public function logout()
    {
        Auth::guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login');
    }

    public function render()
    {
        $user = auth()->user();
        $sundayFirst = $user->week_starts_on === 'sunday';
        $weekStart = now()->startOfWeek($sundayFirst ? Carbon::SUNDAY : Carbon::MONDAY)->startOfDay();
        $weekISOs = collect(range(0, 6))->map(fn ($i) => $weekStart->copy()->addDays($i)->format('Y-m-d'))->all();
        $firstDay = $weekISOs[0];
        $lastDay = $weekISOs[6];

        $realBits = $user->bits()->where('is_system', false)->where('status', 'active')->get();
        $activeBits = $realBits->count();

        $allDone = Entry::where('user_id', $user->id)->where('status', 'done')->get();
        $daysTracked = $allDone->map(fn ($e) => $e->date->format('Y-m-d'))->unique()->count();

        // this-week % (goal-aware, excludes system AND bits not active this week) — mirrors Home.
        $weekEntries = $allDone->filter(fn ($e) => in_array($e->date->format('Y-m-d'), $weekISOs, true));
        $doneSum = 0;
        $goalSum = 0;
        foreach ($realBits as $b) {
            // Skip bits that hadn't started by week's end or ended before it began.
            if ($b->start_date && $b->start_date->format('Y-m-d') > $lastDay) {
                continue;
            }
            if ($b->end_date && $b->end_date->format('Y-m-d') < $firstDay) {
                continue;
            }
            $goal = max(1, $b->goal_per_week);
            $done = $weekEntries->filter(fn ($e) => $e->bit_id === $b->id)->count();
            $doneSum += min($done, $goal);
            $goalSum += $goal;
        }
        $thisWeek = $goalSum ? (int) round($doneSum / $goalSum * 100) : 0;

        $themeLabel = ucfirst($user->theme ?? 'system');
        $langLabel = strtoupper($user->language ?? 'en');
        $weekStartLabel = ucfirst($user->week_starts_on ?? 'monday');

        return view('livewire.profile', [
            'displayName' => $user->name ?: explode('@', $user->email)[0],
            'email' => $user->email,
            'username' => $user->username,
            'bio' => $user->bio,
            'avatarUrl' => $user->avatar_url,
            'activeBits' => $activeBits,
            'daysTracked' => $daysTracked,
            'thisWeek' => $thisWeek,
            'themeLabel' => $themeLabel,
            'langLabel' => $langLabel,
            'weekStartLabel' => $weekStartLabel,
        ]);
    }
}
