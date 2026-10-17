<?php

namespace App\Livewire\Settings;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class MyAd extends Component
{
    public string $businessName = '';
    public string $tagline = '';
    public string $link = '';

    public function mount(): void
    {
        $u = auth()->user();
        $this->businessName = $u->business_name ?? '';
        $this->tagline = $u->ad_tagline ?? '';
        $this->link = $u->ad_link ?? '';
    }

    public function save()
    {
        $data = $this->validate([
            'businessName' => 'nullable|string|max:60',
            'tagline' => 'nullable|string|max:120',
            'link' => 'nullable|url|max:200',
        ]);

        auth()->user()->update([
            'business_name' => $data['businessName'] ?: null,
            'ad_tagline' => $data['tagline'] ?: null,
            'ad_link' => $data['link'] ?: null,
        ]);
        session()->flash('status', 'Ad saved ✓');

        return $this->redirect(route('profile'), navigate: true);
    }

    public function render()
    {
        return view('livewire.settings.my-ad');
    }
}
