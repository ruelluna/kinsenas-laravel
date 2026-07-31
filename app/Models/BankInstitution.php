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
        'features',
    ];

    protected function casts(): array
    {
        return [
            'type' => BankInstitutionType::class,
            'is_active' => 'boolean',
            'features' => 'array',
        ];
    }

    public function supportsSavingsSpaces(): bool
    {
        return isset($this->features['savings_spaces']['max'])
            && (int) $this->features['savings_spaces']['max'] > 0;
    }

    public function maxSavingsSpaces(): int
    {
        if (! $this->supportsSavingsSpaces()) {
            return 0;
        }

        return (int) $this->features['savings_spaces']['max'];
    }

    /**
     * @return array{max: int, main_label: string, space_label_prefix: string}|null
     */
    public function savingsSpacesConfig(): ?array
    {
        if (! $this->supportsSavingsSpaces()) {
            return null;
        }

        $config = $this->features['savings_spaces'];

        return [
            'max' => (int) $config['max'],
            'main_label' => (string) ($config['main_label'] ?? 'Main account'),
            'space_label_prefix' => (string) ($config['space_label_prefix'] ?? 'GoSave'),
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
