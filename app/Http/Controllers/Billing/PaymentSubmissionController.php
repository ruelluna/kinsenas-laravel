<?php

namespace App\Http\Controllers\Billing;

use App\Enums\BillingMode;
use App\Enums\PaymentSubmissionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\SubmitPaymentRequest;
use App\Models\PaymentSubmission;
use App\Services\Marketing\GhlUserTagService;
use App\Support\Marketing\GhlTagCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentSubmissionController extends Controller
{
    public function __construct(private GhlUserTagService $ghlUserTagService) {}

    public function create(Request $request): Response|RedirectResponse
    {
        if (BillingMode::isOpenBeta()) {
            return to_route('settings.billing')
                ->with('error', __('Payments are not accepted during the open beta.'));
        }

        return Inertia::render('billing/pay', [
            'planPriceId' => $request->query('plan_price_id'),
        ]);
    }

    public function store(SubmitPaymentRequest $request): RedirectResponse
    {
        if (BillingMode::isOpenBeta()) {
            return to_route('settings.billing')
                ->with('error', __('Payments are not accepted during the open beta.'));
        }

        $user = $request->user();
        $team = $user->currentTeam;

        abort_if($team === null, 404);

        abort_unless($user->canManageBilling($team), 403);

        $hasPending = PaymentSubmission::query()
            ->where('team_id', $team->id)
            ->where('status', PaymentSubmissionStatus::Pending)
            ->exists();

        if ($hasPending) {
            return back()->withErrors([
                'reference_number' => __('This team already has a pending payment submission.'),
            ]);
        }

        $path = $request->file('proof_image')?->store('payment-proofs', 'public');

        PaymentSubmission::query()->create([
            'user_id' => $user->id,
            'team_id' => $team->id,
            'plan_price_id' => $request->validated('plan_price_id'),
            'reference_number' => $request->validated('reference_number'),
            'proof_image_path' => $path,
        ]);

        $this->ghlUserTagService->dispatch(
            $user,
            [GhlTagCatalog::PAYMENT_SUBMITTED],
            [],
            ['event' => 'payment_submitted', 'team_id' => $team->id],
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Payment submitted for review.')]);

        return to_route('settings.billing');
    }
}
