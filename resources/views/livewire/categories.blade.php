<div>
    <x-page-header title="Categories" :back="route('profile')">
        <button type="button" wire:click="$toggle('adding')" class="hdr-back" aria-label="Add category">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2.4" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </button>
    </x-page-header>

    @if ($adding)
        <div style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:16px; box-shadow:var(--sh-sm); margin-bottom:16px;">
            <input type="text" wire:model="name" class="field-input" placeholder="Category name" maxlength="30" style="margin-bottom:12px;">
            @error('name') <div style="color:var(--danger); font-size:11px; margin:-8px 0 10px;">{{ $message }}</div> @enderror
            <div style="display:flex; gap:10px; margin-bottom:14px;">
                @foreach ($palette as $c)
                    <button type="button" wire:click="$set('color', '{{ $c }}')" style="width:32px; height:32px; border-radius:50%; background:{{ $c }}; cursor:pointer; border:{{ $color === $c ? '3px solid var(--text)' : '3px solid transparent' }}; outline:1px solid var(--border-i);"></button>
                @endforeach
            </div>
            <button type="button" wire:click="add" class="btn btn--grad">Add category</button>
        </div>
    @endif

    <div style="display:flex; flex-direction:column; gap:10px;">
        @forelse ($categories as $c)
            <div wire:key="cat-{{ $c->id }}" style="display:flex; align-items:center; gap:12px; padding:14px 16px; background:var(--surface); border:1px solid var(--border); border-radius:16px; box-shadow:var(--sh-sm);">
                <span style="width:12px; height:12px; border-radius:50%; background:{{ $c->color_hex }}; flex-shrink:0;"></span>
                <div style="flex:1; min-width:0;">
                    <div style="font-size:14px; font-weight:600; color:var(--text);">{{ $c->name }}</div>
                    <div style="font-size:11px; color:var(--text-meta); margin-top:1px;">{{ $c->bits_count }} {{ $c->bits_count === 1 ? 'bit' : 'bits' }}{{ $c->is_system ? ' · system' : '' }}</div>
                </div>
                @unless ($c->is_system)
                    <button type="button" wire:click="delete('{{ $c->id }}')" style="color:var(--danger); background:transparent; border:none; cursor:pointer; font-size:11px; font-weight:600; font-family:inherit;">Delete</button>
                @endunless
            </div>
        @empty
            <div style="text-align:center; padding:40px; color:var(--text-meta); font-size:13px;">No categories yet.</div>
        @endforelse
    </div>
</div>
