<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SavingsPlanPageGuidance extends Model
{
    use HasUuids;

    protected $table = 'savings_plan_page_guidance';

    protected $fillable = [
        'chooser_intro',
        'chooser_video_url',
        'before_choose_note',
        'after_income_rules',
        'after_income_video_url',
    ];

    public static function instance(): self
    {
        return self::query()->firstOrCreate([]);
    }
}
