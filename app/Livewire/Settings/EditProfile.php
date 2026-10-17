<?php

namespace App\Livewire\Settings;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class EditProfile extends Component
{
    public string $name = '';
    public string $username = '';
    public string $bio = '';
    public string $avatar = '';

    public const AVATARS = ['😀', '😎', '🦊', '🐼', '🌟', '🔥', '🌱', '🚀', '🧘', '💪', '🎯', '📚'];

    public function mount(): void
    {
        $u = auth()->user();
        $this->name = $u->name ?? '';
        $this->username = $u->username ?? '';
        $this->bio = $u->bio ?? '';
        $this->avatar = $u->avatar_url && str_starts_with($u->avatar_url, 'emoji:') ? substr($u->avatar_url, 6) : '';
    }

    public function pickAvatar(string $emoji): void
    {
        $this->avatar = $this->avatar === $emoji ? '' : $emoji;
    }

    public function save()
    {
        $data = $this->validate([
            'name' => 'required|string|max:50',
            'username' => 'nullable|string|max:30|alpha_dash',
            'bio' => 'nullable|string|max:160',
        ]);

        auth()->user()->update([
            'name' => $data['name'],
            'username' => $data['username'] ?: null,
            'bio' => $data['bio'] ?: null,
            'avatar_url' => $this->avatar ? 'emoji:'.$this->avatar : null,
        ]);

        session()->flash('status', 'Profile updated ✓');

        return $this->redirect(route('profile'), navigate: true);
    }

    public function render()
    {
        return view('livewire.settings.edit-profile', ['avatars' => self::AVATARS]);
    }
}
