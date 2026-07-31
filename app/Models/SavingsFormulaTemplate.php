<?php

namespace App\Models;

use Database\Factories\SavingsFormulaTemplateFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavingsFormulaTemplate extends Model
{
    /** @use HasFactory<SavingsFormulaTemplateFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'best_for',
        'video_embed_url',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    public function categories(): HasMany
    {
        return $this->hasMany(SavingsFormulaTemplateCategory::class, 'template_id')->orderBy('sort_order');
    }
}
