<div>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('dashboard') }}" class="text-gray-400 text-xl" wire:navigate>&larr;</a>
        <h1 class="text-xl font-bold">New Bit</h1>
    </div>

    <form wire:submit="save" class="space-y-4">
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Title</label>
            <input type="text" wire:model="title" placeholder="e.g. Walk 30 minutes"
                class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
            @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Why (optional)</label>
            <textarea wire:model="why" rows="2" placeholder="What's your motivation?"
                class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            @error('why') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Goal per week</label>
            <select wire:model="goalPerWeek" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                @foreach (range(1, 7) as $n)
                    <option value="{{ $n }}">{{ $n }}&times; / week{{ $n === 7 ? ' (Daily)' : '' }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Start date</label>
            <input type="date" wire:model="startDate"
                class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
            @error('startDate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        @if ($categories->isNotEmpty())
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Category (optional)</label>
                <select wire:model="categoryId" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">None</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl transition">
            <span wire:loading.remove wire:target="save">Create Bit</span>
            <span wire:loading wire:target="save">Creating&hellip;</span>
        </button>
    </form>
</div>
