<?php

namespace App\Models;

use App\Enums\CommunityReportReason;
use App\Enums\CommunityReportStatus;
use Database\Factories\CommunityPostReportFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityPostReport extends Model
{
    /** @use HasFactory<CommunityPostReportFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'community_post_id',
        'reporter_id',
        'reason',
        'details',
        'status',
        'resolved_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'reason' => CommunityReportReason::class,
            'status' => CommunityReportStatus::class,
            'resolved_at' => 'datetime',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(CommunityPost::class, 'community_post_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
