<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EntryResource;
use App\Models\Entry;
use Illuminate\Http\Request;

class EntryController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->user()->entries();
        if ($request->filled('bitId')) {
            $q->where('bit_id', $request->query('bitId'));
        }
        if ($request->filled('from')) {
            $q->whereDate('date', '>=', $request->query('from'));
        }
        if ($request->filled('to')) {
            $q->whereDate('date', '<=', $request->query('to'));
        }

        return EntryResource::collection($q->orderBy('date')->get());
    }

    // PUT — upsert one entry by (bit, date).
    public function upsert(Request $request)
    {
        $data = $request->validate([
            'bitId' => 'required|string',
            'date' => 'required|date_format:Y-m-d',
            'status' => 'required|in:done,missed,skipped',
            'source' => 'nullable|in:manual,reminder_tap,backfill',
        ]);
        $user = $request->user();
        if (! $user->bits()->where('id', $data['bitId'])->exists()) {
            abort(404, 'Bit not found');
        }
        $entry = Entry::updateOrCreate(
            ['bit_id' => $data['bitId'], 'date' => $data['date']],
            [
                'user_id' => $user->id,
                'status' => $data['status'],
                'source' => $data['source'] ?? 'manual',
                'completed_at' => $data['status'] === 'done' ? now() : null,
            ],
        );

        return new EntryResource($entry);
    }

    public function destroy(Request $request)
    {
        $data = $request->validate([
            'bitId' => 'required|string',
            'date' => 'required|date_format:Y-m-d',
        ]);
        $user = $request->user();
        if (! $user->bits()->where('id', $data['bitId'])->exists()) {
            abort(404, 'Bit not found');
        }
        Entry::where('user_id', $user->id)
            ->where('bit_id', $data['bitId'])
            ->whereDate('date', $data['date'])
            ->delete();

        return response()->json(['ok' => true]);
    }
}
