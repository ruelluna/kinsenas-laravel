<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BetaFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class AdminBetaFeedbackController extends Controller
{
    public function index(Request $request): Response
    {
        $feedbacks = BetaFeedback::query()
            ->with(['user', 'team'])
            ->latest()
            ->get();

        Log::info('Admin viewed beta feedback list', [
            'admin_user_id' => $request->user()?->id,
            'count' => $feedbacks->count(),
        ]);

        return Inertia::render('admin/beta-feedback/index', [
            'feedbacks' => $feedbacks->map(fn (BetaFeedback $feedback) => [
                'id' => $feedback->id,
                'message' => $feedback->message,
                'category' => $feedback->category?->value,
                'categoryLabel' => $feedback->category?->label(),
                'userName' => $feedback->user?->name,
                'userEmail' => $feedback->user?->email,
                'teamName' => $feedback->team?->name,
                'createdAt' => $feedback->created_at->toISOString(),
            ]),
        ]);
    }
}
