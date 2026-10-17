<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'title' => $this->title,
            'why' => $this->why,
            'description' => $this->description,
            'goalPerWeek' => (int) $this->goal_per_week,
            'startDate' => $this->start_date?->format('Y-m-d'),
            'endDate' => $this->end_date?->format('Y-m-d'),
            'categoryId' => $this->category_id,
            'goalId' => $this->goal_id,
            'iconKey' => $this->icon_key,
            'reminderEnabled' => (bool) $this->reminder_enabled,
            'reminderTime' => $this->reminder_time,
            'reminderDays' => $this->reminder_days ?? [],
            'isSystem' => (bool) $this->is_system,
            'status' => $this->status,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}
