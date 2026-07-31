<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePaymentQrRequest;
use App\Models\PaymentMethodConfig;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminPaymentQrController extends Controller
{
    public function edit(): Response
    {
        $config = PaymentMethodConfig::query()->where('provider', 'manual_paymaya')->first();

        return Inertia::render('admin/payment-qr/edit', [
            'config' => $config ? [
                'label' => $config->label,
                'instructions' => $config->instructions,
                'qrImageUrl' => $config->qr_image_path ? asset('storage/'.$config->qr_image_path) : null,
                'isActive' => $config->is_active,
            ] : null,
        ]);
    }

    public function update(UpdatePaymentQrRequest $request): RedirectResponse
    {
        $config = PaymentMethodConfig::query()->firstOrCreate(
            ['provider' => 'manual_paymaya'],
            ['label' => 'PayMaya', 'is_active' => false],
        );

        $path = $config->qr_image_path;

        if ($request->hasFile('qr_image')) {
            $path = $request->file('qr_image')->store('payment-qr', 'public');
        }

        $config->update([
            'label' => $request->validated('label'),
            'instructions' => $request->validated('instructions'),
            'qr_image_path' => $path,
            'is_active' => $request->boolean('is_active'),
        ]);

        PaymentMethodConfig::query()
            ->where('id', '!=', $config->id)
            ->update(['is_active' => false]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Payment QR updated.')]);

        return back();
    }
}
