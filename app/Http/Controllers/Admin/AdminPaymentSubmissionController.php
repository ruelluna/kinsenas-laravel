<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BillingInterval;
use App\Enums\PaymentSubmissionStatus;
use App\Http\Controllers\Controller;
use App\Models\PaymentSubmission;
use App\Services\Billing\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminPaymentSubmissionController extends Controller
{
    public function __construct(private SubscriptionService $subscriptionService)
    {
    }

    public function index(): Response
    {
        $submissions = PaymentSubmission::query()
            ->with(['user', 'planPrice.plan'])
            ->latest()
            ->get();

        return Inertia::render('admin/payment-submissions/index', [
            'submissions' => $submissions->map(fn (PaymentSubmission $s) => [
                'id' => $s->id,
                'referenceNumber' => $s->reference_number,
                'status' => $s->status->value,
                'userName' => $s->user?->name,
                'userEmail' => $s->user?->email,
                'planName' => $s->planPrice?->plan?->name,
                'interval' => $s->planPrice?->interval?->label(),
                'amount' => $s->planPrice?->amount,
                'createdAt' => $s->created_at->toISOString(),
            ]),
        ]);
    }

    public function approve(Request $request, PaymentSubmission $submission): RedirectResponse
    {
        $submission->update([
            'status' => PaymentSubmissionStatus::Approved,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $this->subscriptionService->activate(
            $submission->user,
            $submission->planPrice->interval ?? BillingInterval::Monthly,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Payment approved.')]);

        return back();
    }

    public function reject(Request $request, PaymentSubmission $submission): RedirectResponse
    {
        $submission->update([
            'status' => PaymentSubmissionStatus::Rejected,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'notes' => $request->input('notes'),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Payment rejected.')]);

        return back();
    }
}
