<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx-guest')]
class Onboarding extends Component
{
    public int $step = 1;

    public function mount()
    {
        if (auth()->user()->onboarding_completed_at) {
            return $this->redirectRoute('dashboard', navigate: true);
        }
    }

    public function next(): void
    {
        if ($this->step < 3) {
            $this->step++;
        }
    }

    public function finish()
    {
        auth()->user()->update(['onboarding_completed_at' => now()]);

        return $this->redirectRoute('dashboard', navigate: true);
    }

    public function render()
    {
        $slides = [
            1 => [
                'bg' => 'linear-gradient(145deg, #FDDCCC 0%, #FABCBC 100%)',
                'svg' => '<circle cx="28" cy="28" r="24" stroke="#E05050" stroke-width="2.5" fill="none"/><circle cx="28" cy="28" r="16" stroke="#E05050" stroke-width="2.5" fill="none"/><circle cx="28" cy="28" r="8" stroke="#E05050" stroke-width="2.5" fill="none"/><circle cx="28" cy="28" r="3.5" fill="#E05050"/>',
                'title' => 'Set meaningful goals',
                'sub' => 'Create Bits that align with your values and watch them compound into lasting change.',
            ],
            2 => [
                'bg' => 'linear-gradient(145deg, #C8EEE4 0%, #A8E4D4 100%)',
                'svg' => '<path d="M8 38L20 24L30 30L44 14" stroke="#1FA882" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M36 14H44V22" stroke="#1FA882" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>',
                'title' => 'Track your progress',
                'sub' => 'Visualize your growth with beautiful charts and celebrate every milestone along the way.',
            ],
            3 => [
                'bg' => 'linear-gradient(145deg, #DDD4F8 0%, #C8C0F4 100%)',
                'svg' => '<path d="M26 4L6 32H22L18 52L38 24H22L26 4Z" fill="#6B52D4" stroke="#6B52D4" stroke-width="1.5" stroke-linejoin="round"/>',
                'title' => 'Stay motivated',
                'sub' => 'Get daily reminders and insights that keep you focused on what matters most.',
            ],
        ];

        return view('livewire.onboarding', ['slide' => $slides[$this->step]]);
    }
}
