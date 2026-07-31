<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsFormulaTemplateCategory extends Model
{
    use HasUuids;

    protected $fillable = [
        'template_id',
        'name',
        'percentage',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:2',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(SavingsFormulaTemplate::class, 'template_id');
    }
}
