<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\StoreSurveyResponseRequest;
use App\Jobs\SyncSurveyResponseToGhl;
use App\Models\SurveyResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SurveyResponseController extends Controller
{
    public function store(StoreSurveyResponseRequest $request): JsonResponse
    {
        $surveyResponse = SurveyResponse::query()->create([
            'language' => $request->validated('language'),
            'email' => $request->validated('email'),
            'name' => $request->validated('name'),
            'result' => $request->validated('result'),
            'answers' => $request->validated('answers'),
            'completed_at' => $request->validated('completed_at'),
            'ip_address' => $request->ip(),
        ]);

        Log::info('Survey response stored', [
            'survey_response_id' => $surveyResponse->id,
            'language' => $surveyResponse->language->value,
            'result' => $surveyResponse->result->value,
            'email' => $surveyResponse->email,
        ]);

        SyncSurveyResponseToGhl::dispatch($surveyResponse)->afterCommit();

        return response()->json([
            'id' => $surveyResponse->id,
        ], 201);
    }
}
