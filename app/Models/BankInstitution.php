<?php

namespace App\Models;

use App\Enums\BankInstitutionType;
use Database\Factories\BankInstitutionFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankInstitution extends Model
{
    /** @use HasFactory<BankInstitutionFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'logo_path',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => BankInstitutionType::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return Attribute<?string, never>
     */
    protected function logoUrl(): Attribute
    {
        return Attribute::get(
            fn (): ?string => $this->logo_path !== null ? asset('storage/'.$this->logo_path) : null,
        );
    }
}
