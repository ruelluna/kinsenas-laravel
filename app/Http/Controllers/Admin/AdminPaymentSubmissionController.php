<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BillingInterval;
use App\Enums\PaymentSubmissionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApprovePaymentSubmissionRequest;
use App\Http\Requests\Admin\RejectPaymentSubmissionRequest;
use App\Models\PaymentSubmission;
use App\Services\Billing\SubscriptionService;
use App\Services\Marketing\GhlUserTagService;
use App\Support\Marketing\GhlTagCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AdminPaymentSubmissionController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private GhlUserTagService $ghlUserTagService,
    ) {}

    public function index(Request $request): Response
    {
        $status = $request->query('status', PaymentSubmissionStatus::Pending->value);

        $submissions = PaymentSubmission::query()
            ->with(['user', 'team', 'planPrice.plan'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
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
                'proofImageUrl' => $s->proof_image_path
                    ? Storage::disk('public')->url($s->proof_image_path)
                    : null,
                'notes' => $s->notes,
                'createdAt' => $s->created_at->toISOString(),
            ]),
            'filters' => [
                'status' => $status,
            ],
            'statusOptions' => [
                ['value' => PaymentSubmissionStatus::Pending->value, 'label' => 'Pending'],
                ['value' => PaymentSubmissionStatus::Approved->value, 'label' => 'Approved'],
                ['value' => PaymentSubmissionStatus::Rejected->value, 'label' => 'Rejected'],
                ['value' => 'all', 'label' => 'All'],
            ],
        ]);
    }

    public function approve(ApprovePaymentSubmissionRequest $request, PaymentSubmission $submission): RedirectResponse
    {
        if ($submission->status !== PaymentSubmissionStatus::Pending) {
            throw ValidationException::withMessages([
                'submission' => __('Only pending submissions can be approved.'),
            ]);
        }

        $submission->update([
            'status' => PaymentSubmissionStatus::Approved,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $this->subscriptionService->activate(
            $submission->team,
            $submission->planPrice->interval ?? BillingInterval::Monthly,
        );

        if ($submission->user !== null) {
            $this->ghlUserTagService->dispatch(
                $submission->user,
                [GhlTagCatalog::SUBSCRIPTION_ACTIVE],
                [GhlTagCatalog::PAYMENT_SUBMITTED, GhlTagCatalog::TRIAL_ACTIVE],
                ['event' => 'payment_approved', 'submission_id' => $submission->id],
            );
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Payment approved.')]);

        return back();
    }

    public function reject(RejectPaymentSubmissionRequest $request, PaymentSubmission $submission): RedirectResponse
    {
        if ($submission->status !== PaymentSubmissionStatus::Pending) {
            throw ValidationException::withMessages([
                'submission' => __('Only pending submissions can be rejected.'),
            ]);
        }

        $submission->update([
            'status' => PaymentSubmissionStatus::Rejected,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'notes' => $request->validated('notes'),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Payment rejected.')]);

        return back();
    }
}
