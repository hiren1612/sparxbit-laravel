<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BitResource;
use App\Models\Bit;
use App\Models\BitChange;
use App\Models\Category;
use Illuminate\Http\Request;

class BitController extends Controller
{
    public function index(Request $request)
    {
        $bits = $request->user()->bits()
            ->orderByDesc('is_system')->orderBy('created_at')->get();

        return BitResource::collection($bits);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $data = $this->mapInput($request->all());
        $data['user_id'] = $user->id;
        if (empty($data['start_date'])) {
            $data['start_date'] = now()->format('Y-m-d');
        }
        $data['category_id'] = $this->safeCategory($user->id, $data['category_id'] ?? null);
        $bit = Bit::create($data);

        return new BitResource($bit);
    }

    public function show(Request $request, string $id)
    {
        return new BitResource($request->user()->bits()->findOrFail($id));
    }

    // PATCH — partial update + edit-history logging.
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $bit = $user->bits()->findOrFail($id);
        $input = $request->all();

        $changes = [];
        if (array_key_exists('goalPerWeek', $input) && (int) $input['goalPerWeek'] !== (int) $bit->goal_per_week) {
            $changes[] = ['field' => 'goal', 'from' => (string) $bit->goal_per_week, 'to' => (string) $input['goalPerWeek']];
        }
        if (array_key_exists('startDate', $input) && $input['startDate'] !== $bit->start_date?->format('Y-m-d')) {
            $changes[] = ['field' => 'start', 'from' => $bit->start_date?->format('Y-m-d') ?? '', 'to' => $input['startDate'] ?? ''];
        }
        if (array_key_exists('endDate', $input)) {
            $cur = $bit->end_date?->format('Y-m-d');
            $next = $input['endDate'] ?? null;
            if ($next !== $cur) {
                $changes[] = ['field' => 'end', 'from' => $cur ?? '', 'to' => $next ?? ''];
            }
        }

        $data = $this->mapInput($input);
        if (array_key_exists('category_id', $data)) {
            $data['category_id'] = $this->safeCategory($user->id, $data['category_id']);
        }
        $bit->update($data);

        foreach ($changes as $c) {
            BitChange::create([
                'bit_id' => $bit->id, 'user_id' => $user->id,
                'field' => $c['field'], 'from_val' => $c['from'], 'to_val' => $c['to'],
                'changed_at' => now(),
            ]);
        }

        return new BitResource($bit->fresh());
    }

    // PUT — idempotent create-or-update by client-supplied id (mobile offline sync).
    public function upsert(Request $request, string $id)
    {
        $user = $request->user();
        $existing = Bit::find($id);
        if ($existing && $existing->user_id !== $user->id) {
            abort(404);
        }
        $data = $this->mapInput($request->all());
        $data['user_id'] = $user->id;
        if (empty($data['start_date'])) {
            $data['start_date'] = $existing?->start_date?->format('Y-m-d') ?? now()->format('Y-m-d');
        }
        if (empty($data['title'])) {
            $data['title'] = $existing?->title ?? 'Bit';
        }
        $data['category_id'] = $this->safeCategory($user->id, $data['category_id'] ?? null);

        $bit = Bit::updateOrCreate(['id' => $id], $data);

        return new BitResource($bit->fresh());
    }

    public function destroy(Request $request, string $id)
    {
        $bit = $request->user()->bits()->findOrFail($id);
        if ($bit->is_system) {
            return response()->json(['message' => "The Daily Check-In can't be deleted."], 400);
        }
        $bit->delete();

        return response()->json(['ok' => true]);
    }

    public function allChanges(Request $request)
    {
        return BitChange::where('user_id', $request->user()->id)
            ->orderBy('changed_at')->get()
            ->map(fn ($c) => $this->changeArr($c));
    }

    public function changes(Request $request, string $id)
    {
        $bit = $request->user()->bits()->findOrFail($id);

        return $bit->changes()->orderByDesc('changed_at')->get()
            ->map(fn ($c) => $this->changeArr($c));
    }

    // ---- helpers ----
    private function changeArr(BitChange $c): array
    {
        return [
            'id' => $c->id, 'bitId' => $c->bit_id, 'userId' => $c->user_id,
            'field' => $c->field, 'fromVal' => $c->from_val, 'toVal' => $c->to_val,
            'changedAt' => $c->changed_at?->toISOString(),
        ];
    }

    private function safeCategory($userId, $categoryId): ?string
    {
        if (empty($categoryId)) {
            return null;
        }

        return Category::where('id', $categoryId)->where('user_id', $userId)->exists()
            ? $categoryId : null;
    }

    private function mapInput(array $in): array
    {
        $map = [
            'title' => 'title', 'why' => 'why', 'description' => 'description',
            'goalPerWeek' => 'goal_per_week', 'startDate' => 'start_date', 'endDate' => 'end_date',
            'categoryId' => 'category_id', 'goalId' => 'goal_id', 'iconKey' => 'icon_key',
            'reminderEnabled' => 'reminder_enabled', 'reminderTime' => 'reminder_time',
            'reminderDays' => 'reminder_days', 'status' => 'status',
        ];
        $out = [];
        foreach ($map as $camel => $snake) {
            if (array_key_exists($camel, $in)) {
                $out[$snake] = $in[$camel];
            }
        }

        return $out;
    }
}
