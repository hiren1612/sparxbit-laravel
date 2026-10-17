<?php

namespace App\Livewire;

use App\Models\Feedback as FeedbackModel;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class Feedback extends Component
{
    public string $type = 'general';
    public string $message = '';
    public bool $sent = false;

    public function submit(): void
    {
        $data = $this->validate([
            'type' => 'required|in:bug,feature,general',
            'message' => 'required|string|max:1000',
        ]);

        FeedbackModel::create([
            'user_id' => auth()->id(),
            'type' => $data['type'],
            'message' => $data['message'],
        ]);

        $this->reset('message');
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.feedback');
    }
}
