<?php

namespace App\Livewire\Bits;

use App\Models\Bit;
use App\Models\BitChange;

class Edit extends BitForm
{
    public Bit $bit;

    public function mount(Bit $bit): void
    {
        abort_unless($bit->user_id === auth()->id(), 404);
        $this->bit = $bit;

        $this->title = $bit->title;
        $this->why = $bit->why ?? '';
        $this->description = $bit->description ?? '';
        $this->goalPerWeek = $bit->goal_per_week;
        $this->startDate = $bit->start_date?->format('Y-m-d') ?? auth()->user()->localToday()->format('Y-m-d');
        $this->grandfatheredStart = $this->startDate;
        $this->endDate = $bit->end_date?->format('Y-m-d') ?? '';
        $this->unlimited = $bit->end_date === null;
        $this->categoryId = $bit->category_id;
        $this->reminderOn = (bool) $bit->reminder_enabled;
        $this->reminderTime = $bit->reminder_time ? substr($bit->reminder_time, 0, 5) : '08:00';
        $this->reminderDays = $bit->reminder_days ?? [];
        $this->iconKey = $bit->icon_key ?? 'sparkle';
    }

    protected function mode(): string
    {
        return 'edit';
    }

    public function save()
    {
        $this->validate();
        $payload = $this->payload();

        // Log goal / start / end changes so the detail screen can show them.
        $this->logChange('goal', $this->bit->goal_per_week, $payload['goal_per_week']);
        $this->logChange('start', $this->bit->start_date?->format('Y-m-d'), $payload['start_date']);
        $this->logChange('end', $this->bit->end_date?->format('Y-m-d'), $payload['end_date']);

        $this->bit->update($payload);

        session()->flash('status', 'Changes saved ✓');

        return $this->redirect(route('bits.show', $this->bit->id), navigate: true);
    }

    private function logChange(string $field, $from, $to): void
    {
        if ((string) $from === (string) $to) {
            return;
        }
        BitChange::create([
            'bit_id' => $this->bit->id,
            'user_id' => auth()->id(),
            'field' => $field,
            'from_val' => (string) ($from ?? ''),
            'to_val' => (string) ($to ?? ''),
            'changed_at' => now(),
        ]);
    }
}
