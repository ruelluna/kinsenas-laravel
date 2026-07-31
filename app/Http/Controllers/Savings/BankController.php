<?php

namespace App\Http\Controllers\Savings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Savings\SaveBankRequest;
use App\Models\Bank;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BankController extends Controller
{
    public function index(Request $request, Team $current_team): Response
    {
        return Inertia::render('savings/banks/index', [
            'banks' => $current_team->banks()->orderBy('sort_order')->get()->map(fn (Bank $bank) => [
                'id' => $bank->id,
                'name' => $bank->name,
                'accountLabel' => $bank->account_label,
                'isActive' => $bank->is_active,
            ]),
        ]);
    }

    public function store(SaveBankRequest $request, Team $current_team): RedirectResponse
    {
        $current_team->banks()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Bank added.')]);

        return back();
    }

    public function update(SaveBankRequest $request, Team $current_team, Bank $bank): RedirectResponse
    {
        abort_if($bank->team_id !== $current_team->id, 404);

        $bank->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Bank updated.')]);

        return back();
    }

    public function destroy(Request $request, Team $current_team, Bank $bank): RedirectResponse
    {
        abort_if($bank->team_id !== $current_team->id, 404);

        $bank->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Bank removed.')]);

        return back();
    }
}
