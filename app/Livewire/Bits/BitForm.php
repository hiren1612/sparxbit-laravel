<?php

namespace App\Livewire\Bits;

use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
abstract class BitForm extends Component
{
    public string $title = '';
    public string $why = '';
    public string $description = '';
    public int $goalPerWeek = 5;
    public string $startDate = '';
    public string $endDate = '';
    public bool $unlimited = true;
    public ?string $categoryId = null;
    public bool $reminderOn = false;
    public string $reminderTime = '08:00';
    public array $reminderDays = [];
    public string $iconKey = 'sparkle';

    // New-category modal
    public bool $catModalOpen = false;
    public string $newCatName = '';
    public string $newCatColor = '#2A5FCC';

    public const ICON_OPTIONS = [
        'sparkle' => '✨', 'walk' => '🚶', 'run' => '🏃', 'book' => '📚', 'lotus' => '🧘',
        'droplet' => '💧', 'pen' => '✍️', 'briefcase' => '💼', 'moon' => '🌙', 'heart' => '❤️',
        'star' => '⭐', 'brain' => '🧠', 'target' => '🎯', 'phone' => '📞', 'home' => '🏠', 'users' => '👥',
    ];

    public const PALETTE = ['#1A7A50', '#2A5FCC', '#B85A10', '#B53760', '#5A3FB0', '#7A5A10'];

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:50',
            'why' => 'nullable|string|max:300',
            'description' => 'nullable|string|max:500',
            'goalPerWeek' => 'required|integer|min:1|max:7',
            'startDate' => 'required|date',
            'endDate' => 'nullable|date|after_or_equal:startDate',
            'categoryId' => 'nullable|string',
            'reminderTime' => 'nullable',
        ];
    }

    public function setGoal(int $n): void
    {
        $this->goalPerWeek = max(1, min(7, $n));
    }

    public function setIcon(string $key): void
    {
        $this->iconKey = $key;
    }

    public function toggleDay(int $d): void
    {
        if (in_array($d, $this->reminderDays, true)) {
            $this->reminderDays = array_values(array_filter($this->reminderDays, fn ($x) => $x !== $d));
        } else {
            $this->reminderDays[] = $d;
            sort($this->reminderDays);
        }
    }

    public function createCategory(): void
    {
        $this->validate([
            'newCatName' => 'required|string|max:30',
            'newCatColor' => 'required|string',
        ]);

        $cat = Category::create([
            'user_id' => auth()->id(),
            'name' => $this->newCatName,
            'color_hex' => $this->newCatColor,
            'is_system' => false,
        ]);

        $this->categoryId = $cat->id;
        $this->newCatName = '';
        $this->catModalOpen = false;
    }

    protected function payload(): array
    {
        $categoryId = $this->categoryId ?: null;
        if ($categoryId && ! auth()->user()->categories()->where('id', $categoryId)->exists()) {
            $categoryId = null;
        }

        return [
            'title' => $this->title,
            'why' => $this->why ?: null,
            'description' => $this->description ?: null,
            'goal_per_week' => $this->goalPerWeek,
            'start_date' => $this->startDate,
            'end_date' => $this->unlimited ? null : ($this->endDate ?: null),
            'category_id' => $categoryId,
            'icon_key' => $this->iconKey,
            'reminder_enabled' => $this->reminderOn,
            'reminder_time' => $this->reminderOn ? $this->reminderTime : null,
            'reminder_days' => $this->reminderOn ? $this->reminderDays : [],
        ];
    }

    abstract public function save();

    abstract protected function mode(): string;

    public function render()
    {
        return view('livewire.bits.form', [
            'mode' => $this->mode(),
            'categories' => auth()->user()->categories()->orderBy('name')->get(),
            'iconOptions' => self::ICON_OPTIONS,
            'palette' => self::PALETTE,
        ]);
    }
}
