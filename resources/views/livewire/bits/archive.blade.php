<div>
    <x-page-header title="Archive" :back="route('bits.index')" />

    @if ($archived->isEmpty())
        <div style="text-align:center; padding:48px; color:var(--text-meta);">Nothing archived yet.</div>
    @else
        <div style="display:flex; flex-direction:column; gap:10px;">
            @foreach ($archived as $b)
                <div wire:key="arc-{{ $b['id'] }}" style="display:flex; align-items:center; gap:12px; padding:13px 14px; background:var(--surface); border:1px solid var(--border); border-radius:16px; box-shadow:var(--sh-sm);">
                    <span style="font-size:20px;">{{ $b['emoji'] }}</span>
                    <div style="flex:1; min-width:0;">
                        <div style="font-size:14px; font-weight:600; color:var(--text); margin-bottom:2px;">{{ $b['title'] }}</div>
                        <div style="font-size:11px; color:var(--text-meta);">{{ $b['completion'] }}% completion · Archived {{ $b['archivedAt'] }}</div>
                    </div>
                    <button type="button" wire:click="restore('{{ $b['id'] }}')" style="font-size:11px; font-weight:600; color:var(--primary); background:var(--primary-lt); border:none; border-radius:8px; padding:6px 12px; cursor:pointer; font-family:inherit;">Restore</button>
                    <button type="button" wire:click="$set('confirmDeleteId', '{{ $b['id'] }}')" style="font-size:11px; font-weight:600; color:var(--danger); background:transparent; border:none; cursor:pointer; padding:4px 6px; font-family:inherit;">Delete</button>
                </div>
            @endforeach
        </div>
    @endif

    @if ($confirmDelete)
        <div class="sheet-overlay" style="align-items:center;" wire:click="$set('confirmDeleteId', null)">
            <div wire:click.stop style="width:calc(100% - 48px); max-width:340px; background:var(--surface); border-radius:20px; padding:24px;">
                <div style="font-size:16px; font-weight:700; color:var(--text); margin-bottom:8px;">Delete "{{ $confirmDelete['title'] }}" permanently?</div>
                <div style="font-size:12.5px; color:var(--text-mid); margin-bottom:16px; line-height:1.5;">This removes the Bit and all of its history. This cannot be undone.</div>
                <button type="button" class="btn" wire:click="delete('{{ $confirmDelete['id'] }}')" style="background:var(--danger); color:#fff; margin-bottom:8px;">Delete permanently</button>
                <button type="button" wire:click="$set('confirmDeleteId', null)" style="width:100%; height:44px; border-radius:12px; background:transparent; border:none; font-size:13px; font-weight:600; color:var(--text-mid); cursor:pointer; font-family:inherit;">Cancel</button>
            </div>
        </div>
    @endif
</div>
