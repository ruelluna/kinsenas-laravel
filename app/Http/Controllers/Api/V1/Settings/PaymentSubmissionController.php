<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Enums\BillingMode;
use App\Enums\PaymentSubmissionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\SubmitPaymentRequest;
use App\Models\PaymentSubmission;
use App\Services\Marketing\GhlUserTagService;
use App\Support\Marketing\GhlTagCatalog;
use Illuminate\Http\JsonResponse;

class PaymentSubmissionController extends Controller
{
    public function __construct(private GhlUserTagService $ghlUserTagService) {}

    public function store(SubmitPaymentRequest $request): JsonResponse
    {
        abort_if(BillingMode::isOpenBeta(), 403, __('Payments are not accepted during the open beta.'));

        $user = $request->user();
        $team = $user->currentTeam;

        abort_if($team === null, 404);
        abort_unless($user->canManageBilling($team), 403);

        $hasPending = PaymentSubmission::query()
            ->where('team_id', $team->id)
            ->where('status', PaymentSubmissionStatus::Pending)
            ->exists();

        if ($hasPending) {
            return response()->json([
                'message' => __('This team already has a pending payment submission.'),
                'errors' => [
                    'reference_number' => [__('This team already has a pending payment submission.')],
                ],
            ], 422);
        }

        $path = $request->file('proof_image')?->store('payment-proofs', 'public');

        $submission = PaymentSubmission::query()->create([
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

        return response()->json([
            'message' => __('Payment submitted for review.'),
            'paymentSubmission' => [
                'id' => $submission->id,
                'status' => $submission->status->value,
                'referenceNumber' => $submission->reference_number,
                'createdAt' => $submission->created_at?->toISOString(),
            ],
        ], 201);
    }
}
