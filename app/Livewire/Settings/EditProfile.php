<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.sparx')]
class EditProfile extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $username = '';
    public string $bio = '';

    // Current stored avatar value: "emoji:X", a "/storage/…" photo path, or ''.
    public string $currentAvatar = '';
    // Newly chosen photo (temporary upload, not yet saved).
    public $photo = null;

    public const AVATARS = ['😀', '😎', '🤓', '🧠', '💪', '🚀', '✨', '🌱', '🔥', '⭐', '🌟', '💎', '🎯', '🌈', '🦄', '🐱', '🐶', '🌸', '🍀', '☕'];

    public function mount(): void
    {
        $u = auth()->user();
        $this->name = $u->name ?? '';
        $this->username = $u->username ?? '';
        $this->bio = $u->bio ?? '';
        $this->currentAvatar = $u->avatar_url ?? '';
    }

    public function updatedPhoto(): void
    {
        $this->validate(['photo' => 'image|max:4096']); // 4 MB
    }

    public function pickAvatar(string $emoji): void
    {
        $this->currentAvatar = $this->currentAvatar === 'emoji:'.$emoji ? '' : 'emoji:'.$emoji;
        $this->photo = null; // emoji replaces any pending photo
    }

    public function removeAvatar(): void
    {
        $this->currentAvatar = '';
        $this->photo = null;
    }

    public function save()
    {
        $data = $this->validate([
            'name' => 'required|string|max:50',
            'username' => 'nullable|string|max:30|alpha_dash',
            'bio' => 'nullable|string|max:160',
            'photo' => 'nullable|image|max:4096',
        ]);

        $avatarUrl = $this->currentAvatar ?: null;

        if ($this->photo) {
            // Store the uploaded photo and point the avatar at its public URL.
            $path = $this->photo->store('avatars', 'public');
            $avatarUrl = Storage::url($path);
        }

        auth()->user()->update([
            'name' => $data['name'],
            'username' => $data['username'] ?: null,
            'bio' => $data['bio'] ?: null,
            'avatar_url' => $avatarUrl,
        ]);

        session()->flash('status', 'Profile updated ✓');

        return $this->redirect(route('profile'), navigate: true);
    }

    public function render()
    {
        return view('livewire.settings.edit-profile', ['avatars' => self::AVATARS]);
    }
}
