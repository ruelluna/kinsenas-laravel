<?php

namespace App\Http\Controllers\Savings;

use App\Enums\RecipientType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Savings\SaveRecipientRequest;
use App\Models\Recipient;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecipientController extends Controller
{
    public function index(Request $request, Team $current_team): Response
    {
        return Inertia::render('savings/recipients/index', [
            'recipients' => $current_team->recipients()->latest()->get()->map(fn (Recipient $recipient) => [
                'id' => $recipient->id,
                'type' => $recipient->type->value,
                'typeLabel' => $recipient->type->label(),
                'name' => $recipient->name,
                'notes' => $recipient->notes,
            ]),
            'recipientTypes' => RecipientType::options(),
        ]);
    }

    public function store(SaveRecipientRequest $request, Team $current_team): RedirectResponse
    {
        $current_team->recipients()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Recipient added.')]);

        return back();
    }

    public function update(SaveRecipientRequest $request, Team $current_team, Recipient $recipient): RedirectResponse
    {
        abort_if($recipient->team_id !== $current_team->id, 404);

        $recipient->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Recipient updated.')]);

        return back();
    }

    public function destroy(Request $request, Team $current_team, Recipient $recipient): RedirectResponse
    {
        abort_if($recipient->team_id !== $current_team->id, 404);

        $recipient->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Recipient removed.')]);

        return back();
    }
}
