<?php

namespace App\Models;

use App\Enums\SurveyLanguage;
use App\Enums\SurveyResultSlug;
use Database\Factories\SurveyResponseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    /** @use HasFactory<SurveyResponseFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'language',
        'email',
        'name',
        'result',
        'answers',
        'completed_at',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'language' => SurveyLanguage::class,
            'result' => SurveyResultSlug::class,
            'answers' => 'array',
            'completed_at' => 'datetime',
        ];
    }
}
