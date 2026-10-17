<div>
    <x-page-header title="Edit profile" :back="route('profile')">
        <button type="button" wire:click="save" style="height:36px; padding:0 14px; border-radius:11px; background:var(--grad); border:none; color:#fff; font-size:13px; font-weight:600; cursor:pointer; box-shadow:var(--sh-btn);">Save</button>
    </x-page-header>

    <div style="display:flex; justify-content:center; margin-bottom:8px;">
        <div style="width:80px; height:80px; border-radius:24px; background:var(--grad); display:flex; align-items:center; justify-content:center; font-size:{{ $avatar ? '46px' : '28px' }}; font-weight:700; color:#fff; box-shadow:0 4px 16px rgba(91,141,239,.2);">
            {{ $avatar ?: strtoupper(substr($name ?: 'U', 0, 1)) }}
        </div>
    </div>
    <div style="display:flex; flex-wrap:wrap; gap:8px; justify-content:center; margin-bottom:22px;">
        @foreach ($avatars as $e)
            <button type="button" wire:click="pickAvatar('{{ $e }}')" style="width:40px; height:40px; border-radius:12px; font-size:20px; cursor:pointer; border:{{ $avatar === $e ? '2px solid var(--primary)' : '1px solid var(--border-i)' }}; background:{{ $avatar === $e ? 'var(--primary-lt)' : 'var(--surface)' }};">{{ $e }}</button>
        @endforeach
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
