<?php

namespace App\Http\Controllers\Api\V1\Savings;

use App\Enums\RecipientType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Savings\SaveRecipientRequest;
use App\Models\Recipient;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecipientController extends Controller
{
    public function index(Team $team): JsonResponse
    {
        return response()->json([
            'data' => $team->recipients()->latest()->get()->map(
                fn (Recipient $recipient) => $this->payload($recipient),
            )->values(),
            'meta' => ['recipientTypes' => RecipientType::options()],
        ]);
    }

    public function store(SaveRecipientRequest $request, Team $team): JsonResponse
    {
        $recipient = $team->recipients()->create($request->validated());

        return response()->json(['data' => $this->payload($recipient)], 201);
    }

    public function update(SaveRecipientRequest $request, Team $team, Recipient $recipient): JsonResponse
    {
        abort_if($recipient->team_id !== $team->id, 404);

        $recipient->update($request->validated());

        return response()->json(['data' => $this->payload($recipient->fresh())]);
    }

    public function destroy(Request $request, Team $team, Recipient $recipient): JsonResponse
    {
        abort_if($recipient->team_id !== $team->id, 404);

        $recipient->delete();

        return response()->noContent();
    }

    private function payload(Recipient $recipient): array
    {
        return [
            'id' => $recipient->id,
            'type' => $recipient->type->value,
            'typeLabel' => $recipient->type->label(),
            'name' => $recipient->name,
            'notes' => $recipient->notes,
        ];
    }
}
