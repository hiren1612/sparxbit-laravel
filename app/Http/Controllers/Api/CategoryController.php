<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->categories()
            ->orderByDesc('is_system')->orderBy('created_at')->get()
            ->map(fn ($c) => $this->out($c));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:30',
            'colorHex' => 'nullable|string',
        ]);
        $cat = Category::create([
            'user_id' => $request->user()->id,
            'name' => $data['name'],
            'color_hex' => $data['colorHex'] ?? '#6366F1',
        ]);

        return response()->json($this->out($cat));
    }

    public function update(Request $request, string $id)
    {
        $cat = $request->user()->categories()->findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string|max:30',
            'colorHex' => 'sometimes|string',
        ]);
        if (array_key_exists('name', $data)) {
            $cat->name = $data['name'];
        }
        if (array_key_exists('colorHex', $data)) {
            $cat->color_hex = $data['colorHex'];
        }
        $cat->save();

        return response()->json($this->out($cat));
    }

    public function destroy(Request $request, string $id)
    {
        $cat = $request->user()->categories()->findOrFail($id);
        if ($cat->is_system) {
            return response()->json(['message' => 'System categories cannot be deleted.'], 400);
        }
        $cat->delete(); // bits' category_id is set null by the FK

        return response()->json(['ok' => true]);
    }

    private function out(Category $c): array
    {
        return [
            'id' => $c->id,
            'userId' => $c->user_id,
            'name' => $c->name,
            'colorHex' => $c->color_hex,
            'isSystem' => (bool) $c->is_system,
            'createdAt' => $c->created_at?->toISOString(),
        ];
    }
}
