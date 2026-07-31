<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\SubmitPaymentRequest;
use App\Enums\PaymentSubmissionStatus;
use App\Models\PaymentSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentSubmissionController extends Controller
{
    public function create(Request $request): Response
    {
        return Inertia::render('billing/pay', [
            'planPriceId' => $request->query('plan_price_id'),
        ]);
    }

    public function store(SubmitPaymentRequest $request): RedirectResponse
    {
        $hasPending = PaymentSubmission::query()
            ->where('user_id', $request->user()->id)
            ->where('status', PaymentSubmissionStatus::Pending)
            ->exists();

        if ($hasPending) {
            return back()->withErrors([
                'reference_number' => __('You already have a pending payment submission.'),
            ]);
        }

        $path = $request->file('proof_image')?->store('payment-proofs', 'public');

        PaymentSubmission::query()->create([
            'user_id' => $request->user()->id,
            'plan_price_id' => $request->validated('plan_price_id'),
            'reference_number' => $request->validated('reference_number'),
            'proof_image_path' => $path,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Payment submitted for review.')]);

        return to_route('settings.billing');
    }
}
