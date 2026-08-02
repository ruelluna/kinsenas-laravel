<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBetaAccessCodeBatchRequest;
use App\Http\Requests\Admin\StoreBetaAccessCodeRequest;
use App\Http\Requests\Admin\UpdateBetaAccessCodeRequest;
use App\Models\BetaAccessCode;
use App\Models\BetaAccessCodeBatch;
use App\Services\Billing\BetaAccessCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminBetaAccessCodeController extends Controller
{
    public function __construct(private BetaAccessCodeService $betaAccessCodeService) {}

    public function index(Request $request): Response
    {
        $status = $request->query('status', 'active');

        $codes = BetaAccessCode::query()
            ->with('batch')
            ->when($status === 'active', fn ($query) => $query
                ->where('is_active', true)
                ->where(fn ($query) => $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now())))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when($status === 'expired', fn ($query) => $query
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', now()))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/beta-access-codes/index', [
            'codes' => $codes->through(fn (BetaAccessCode $code) => $this->mapCode($code)),
            'filters' => [
                'status' => $status,
            ],
            'statusOptions' => [
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'inactive', 'label' => 'Inactive'],
                ['value' => 'expired', 'label' => 'Expired'],
                ['value' => 'all', 'label' => 'All'],
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/beta-access-codes/create');
    }

    public function store(StoreBetaAccessCodeRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->betaAccessCodeService->createSharedCode(
            admin: $request->user(),
            code: $validated['code'],
            label: $validated['label'],
            maxUses: $validated['max_uses'] ?? null,
            expiresAt: isset($validated['expires_at'])
                ? Carbon::parse($validated['expires_at'])
                : null,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Beta access code created.')]);

        return to_route('admin.beta-access-codes.index');
    }

    public function storeBatch(StoreBetaAccessCodeBatchRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        [$batch] = $this->betaAccessCodeService->createSingleUseBatch(
            admin: $request->user(),
            name: $validated['name'],
            quantity: (int) $validated['quantity'],
            expiresAt: isset($validated['expires_at'])
                ? Carbon::parse($validated['expires_at'])
                : null,
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Generated :count single-use beta access codes.', ['count' => $batch->quantity]),
        ]);

        return to_route('admin.beta-access-codes.index');
    }

    public function update(UpdateBetaAccessCodeRequest $request, BetaAccessCode $betaAccessCode): RedirectResponse
    {
        $validated = $request->validated();

        $betaAccessCode->forceFill([
            'is_active' => array_key_exists('is_active', $validated)
                ? (bool) $validated['is_active']
                : $betaAccessCode->is_active,
            'max_uses' => array_key_exists('max_uses', $validated)
                ? $validated['max_uses']
                : $betaAccessCode->max_uses,
            'expires_at' => array_key_exists('expires_at', $validated)
                ? ($validated['expires_at'] !== null ? Carbon::parse($validated['expires_at']) : null)
                : $betaAccessCode->expires_at,
        ])->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Beta access code updated.')]);

        return back();
    }

    public function exportBatch(BetaAccessCodeBatch $batch): StreamedResponse
    {
        $filename = str($batch->name)->slug().'-beta-codes.csv';

        return response()->streamDownload(function () use ($batch) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['code', 'label', 'type', 'max_uses', 'expires_at']);

            $batch->codes()
                ->orderBy('code')
                ->each(function (BetaAccessCode $code) use ($handle) {
                    fputcsv($handle, [
                        $code->code,
                        $code->label,
                        $code->type->value,
                        $code->max_uses,
                        $code->expires_at?->toDateTimeString(),
                    ]);
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapCode(BetaAccessCode $code): array
    {
        return [
            'id' => $code->id,
            'code' => $code->code,
            'label' => $code->label,
            'type' => $code->type->value,
            'typeLabel' => $code->type->label(),
            'maxUses' => $code->max_uses,
            'redemptionsCount' => $code->redemptions_count,
            'remainingUses' => $code->remainingUses(),
            'expiresAt' => $code->expires_at?->toISOString(),
            'isActive' => $code->is_active,
            'isRedeemable' => $code->isRedeemable(),
            'batchId' => $code->batch_id,
            'batchName' => $code->batch?->name,
            'createdAt' => $code->created_at?->toISOString(),
        ];
    }
}
