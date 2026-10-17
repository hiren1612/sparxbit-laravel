<?php

namespace App\Livewire;

use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.sparx')]
class Categories extends Component
{
    public bool $adding = false;
    public string $name = '';
    public string $color = '#2A5FCC';

    public const PALETTE = ['#1A7A50', '#2A5FCC', '#B85A10', '#B53760', '#5A3FB0', '#7A5A10'];

    public function add(): void
    {
        $data = $this->validate([
            'name' => 'required|string|max:30',
            'color' => 'required|string',
        ]);

        Category::create([
            'user_id' => auth()->id(),
            'name' => $data['name'],
            'color_hex' => $data['color'],
            'is_system' => false,
        ]);

        $this->reset('name', 'adding');
        $this->color = '#2A5FCC';
    }

    public function delete(string $id): void
    {
        $cat = auth()->user()->categories()->where('id', $id)->where('is_system', false)->first();
        $cat?->delete();
    }

    public function render()
    {
        return view('livewire.categories', [
            'categories' => auth()->user()->categories()->withCount('bits')->orderByDesc('is_system')->orderBy('name')->get(),
            'palette' => self::PALETTE,
        ]);
    }
}
