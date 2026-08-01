<?php

namespace App\Models;

use App\Enums\BetaFeedbackCategory;
use Database\Factories\BetaFeedbackFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BetaFeedback extends Model
{
    /** @use HasFactory<BetaFeedbackFactory> */
    use HasFactory, HasUuids;

    protected $table = 'beta_feedbacks';

    protected $fillable = [
        'user_id',
        'team_id',
        'message',
        'category',
    ];

    protected function casts(): array
    {
        return [
            'category' => BetaFeedbackCategory::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
