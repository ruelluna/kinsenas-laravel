<?php

namespace App\Models;

use App\Enums\CommunityPostStatus;
use Database\Factories\CommunityPostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityPost extends Model
{
    /** @use HasFactory<CommunityPostFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'cover_image_url',
        'status',
        'rejection_reason',
        'published_at',
        'moderated_by',
        'moderated_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => CommunityPostStatus::class,
            'published_at' => 'datetime',
            'moderated_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(CommunityCategory::class, 'community_post_category');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(CommunityPostReport::class);
    }

    /**
     * @param  Builder<CommunityPost>  $query
     * @return Builder<CommunityPost>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', CommunityPostStatus::Published);
    }

    /**
     * @param  Builder<CommunityPost>  $query
     * @return Builder<CommunityPost>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', CommunityPostStatus::Pending);
    }

    /**
     * Posts visible on the admin community list (excludes withdrawn/removals).
     *
     * @param  Builder<CommunityPost>  $query
     * @return Builder<CommunityPost>
     */
    public function scopeListedInAdmin(Builder $query): Builder
    {
        return $query->where('status', '!=', CommunityPostStatus::Withdrawn);
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }
}
