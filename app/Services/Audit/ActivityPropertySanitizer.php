<?php

namespace App\Services\Audit;

class ActivityPropertySanitizer
{
    /** @var list<string> */
    private const FORBIDDEN_KEY_FRAGMENTS = [
        'amount',
        'balance',
        'password',
        'passphrase',
        'secret',
        'token',
        'encrypted',
        'ciphertext',
        'proof',
        'receipt',
    ];

    /** @var list<string> */
    private const FORBIDDEN_TOP_LEVEL_KEYS = [
        'old',
        'attributes',
        'changes',
        'attribute_changes',
    ];

    /**
     * @param  array<string, mixed>  $properties
     * @return array<string, mixed>
     */
    public function sanitize(array $properties): array
    {
        return $this->stripForbidden($properties);
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    public function containsForbiddenKeys(array $properties): bool
    {
        foreach ($properties as $key => $value) {
            if ($this->isForbiddenKey((string) $key)) {
                return true;
            }

            if (is_array($value) && $this->containsForbiddenKeys($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $properties
     * @return array<string, mixed>
     */
    private function stripForbidden(array $properties): array
    {
        $sanitized = [];

        foreach ($properties as $key => $value) {
            if ($this->isForbiddenKey((string) $key)) {
                continue;
            }

            if (is_array($value)) {
                $nested = $this->stripForbidden($value);

                if ($nested !== []) {
                    $sanitized[$key] = $nested;
                }

                continue;
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    private function isForbiddenKey(string $key): bool
    {
        $normalized = strtolower($key);

        if (in_array($normalized, self::FORBIDDEN_TOP_LEVEL_KEYS, true)) {
            return true;
        }

        foreach (self::FORBIDDEN_KEY_FRAGMENTS as $fragment) {
            if (str_contains($normalized, $fragment)) {
                return true;
            }
        }

        return false;
    }
}
