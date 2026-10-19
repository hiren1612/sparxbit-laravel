<div>
    <x-page-header title="Edit profile" :back="route('profile')">
        <button type="button" wire:click="save" style="height:36px; padding:0 14px; border-radius:11px; background:var(--grad); border:none; color:#fff; font-size:13px; font-weight:600; cursor:pointer; box-shadow:var(--sh-btn);">Save</button>
    </x-page-header>

    @php $isEmoji = str_starts_with($currentAvatar, 'emoji:'); @endphp

    {{-- Avatar preview --}}
    <div style="display:flex; flex-direction:column; align-items:center; gap:14px; margin-bottom:22px;">
        <div style="width:80px; height:80px; border-radius:24px; overflow:hidden; background:var(--grad); display:flex; align-items:center; justify-content:center; font-size:28px; font-weight:700; color:#fff; box-shadow:0 4px 16px rgba(91,141,239,.2);">
            @if ($photo)
                <img src="{{ $photo->temporaryUrl() }}" alt="avatar" style="width:100%; height:100%; object-fit:cover;">
            @elseif ($isEmoji)
                <span style="font-size:46px;">{{ substr($currentAvatar, 6) }}</span>
            @elseif ($currentAvatar)
                <img src="{{ $currentAvatar }}" alt="avatar" style="width:100%; height:100%; object-fit:cover;">
            @else
                {{ strtoupper(substr($name ?: 'U', 0, 1)) }}
            @endif
        </div>

        {{-- Upload / change / remove --}}
        <div style="display:flex; gap:14px; align-items:center;">
            <label style="font-size:12px; font-weight:600; color:var(--primary); cursor:pointer;">
                <span wire:loading.remove wire:target="photo">{{ ($photo || $currentAvatar) ? 'Change photo' : 'Add photo' }}</span>
                <span wire:loading wire:target="photo">Uploading…</span>
                <input type="file" wire:model="photo" accept="image/jpeg,image/png,image/webp" style="display:none;">
            </label>
            @if ($photo || $currentAvatar)
                <button type="button" wire:click="removeAvatar" style="font-size:12px; font-weight:600; color:var(--danger); background:none; border:none; cursor:pointer; padding:0; font-family:inherit;">Remove</button>
            @endif
        </div>
        @error('photo') <div style="color:var(--danger); font-size:11px;">{{ $message }}</div> @enderror

        {{-- Emoji picker --}}
        <div style="width:100%; margin-top:2px;">
            <div style="font-size:10px; font-weight:600; color:var(--text-meta); letter-spacing:1.1px; text-transform:uppercase; margin-bottom:8px;">Or pick an emoji</div>
            <div style="display:flex; flex-wrap:wrap; gap:8px;">
                @foreach ($avatars as $e)
                    <button type="button" wire:click="pickAvatar('{{ $e }}')"
                        style="width:38px; height:38px; border-radius:10px; font-size:20px; cursor:pointer; display:flex; align-items:center; justify-content:center; font-family:inherit;
                        border:1px solid {{ $currentAvatar === 'emoji:'.$e ? 'var(--primary-mid)' : 'var(--border)' }};
                        background:{{ $currentAvatar === 'emoji:'.$e ? 'var(--primary-lt)' : 'var(--surface)' }};">{{ $e }}</button>
                @endforeach
            </div>
        </div>
    </div>

    <div class="field-label">Name</div>
    <input type="text" wire:model="name" class="field-input" maxlength="50" style="margin-bottom:4px;">
    @error('name') <div style="color:var(--danger); font-size:11px; margin-bottom:10px;">{{ $message }}</div> @else <div style="margin-bottom:16px;"></div> @enderror

    <div class="field-label">Username</div>
    <input type="text" wire:model="username" class="field-input" maxlength="30" placeholder="username" style="margin-bottom:4px;">
    @error('username') <div style="color:var(--danger); font-size:11px; margin-bottom:10px;">{{ $message }}</div> @else <div style="margin-bottom:16px;"></div> @enderror

    <div class="field-label">Bio</div>
    <textarea wire:model="bio" rows="3" maxlength="160" class="field-input" placeholder="A short line about you…"></textarea>
</div>
