<div>
    <div style="display:flex; align-items:center; gap:12px; margin-bottom:18px;">
        <a href="{{ route('dashboard') }}" wire:navigate style="color:var(--text-muted); font-size:22px; line-height:1;">&larr;</a>
        <div class="greeting-name" style="font-size:19px;">New Bit</div>
    </div>

    <form wire:submit="save" style="display:flex; flex-direction:column; gap:14px;">
        <div>
            <div class="field-label">Title</div>
            <input type="text" wire:model="title" class="field-input" placeholder="e.g. Walk 30 minutes">
            @error('title') <div style="color:var(--danger); font-size:11px; margin-top:4px;">{{ $message }}</div> @enderror
        </div>

        <div>
            <div class="field-label">Why (optional)</div>
            <textarea wire:model="why" rows="2" class="field-input" placeholder="What's your motivation?"></textarea>
            @error('why') <div style="color:var(--danger); font-size:11px; margin-top:4px;">{{ $message }}</div> @enderror
        </div>

        <div>
            <div class="field-label">Goal per week</div>
            <select wire:model="goalPerWeek" class="field-input">
                @foreach (range(1, 7) as $n)
                    <option value="{{ $n }}">{{ $n }}&times; / week{{ $n === 7 ? ' (Daily)' : '' }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <div class="field-label">Start date</div>
            <input type="date" wire:model="startDate" class="field-input">
            @error('startDate') <div style="color:var(--danger); font-size:11px; margin-top:4px;">{{ $message }}</div> @enderror
        </div>

        @if ($categories->isNotEmpty())
            <div>
                <div class="field-label">Category (optional)</div>
                <select wire:model="categoryId" class="field-input">
                    <option value="">None</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <button type="submit" class="btn btn--grad" style="margin-top:4px;">
            <span wire:loading.remove wire:target="save">Create Bit</span>
            <span wire:loading wire:target="save">Creating&hellip;</span>
        </button>
    </form>
</div>
