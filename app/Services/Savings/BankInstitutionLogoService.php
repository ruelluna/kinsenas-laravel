<?php

namespace App\Services\Savings;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BankInstitutionLogoService
{
    private const LOGO_EXTENSIONS = ['png', 'svg', 'jpg', 'jpeg', 'webp', 'ico'];

    public function ensureLogo(string $slug, string $logoUrl, ?string $existingPath = null): ?string
    {
        $resolvedPath = $this->resolveExistingLogoPath($slug, $existingPath);

        if ($resolvedPath !== null) {
            return $resolvedPath;
        }

        $response = Http::timeout(15)
            ->retry(2, 1000, throw: false)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; FutureSave/1.0; +https://financial-literacy.test)',
                'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
            ])
            ->get($logoUrl);

        if (! $response->successful()) {
            $contentType = Str::lower((string) $response->header('Content-Type'));
            $contentType = Str::before($contentType, ';');
            $hasImageBody = Str::startsWith($contentType, 'image/') && strlen($response->body()) > 0;

            if (! $hasImageBody) {
                Log::warning('Bank institution logo download failed.', [
                    'slug' => $slug,
                    'url' => $logoUrl,
                    'status' => $response->status(),
                ]);

                return null;
            }
        }

        $contentType = Str::lower((string) $response->header('Content-Type'));
        $contentType = Str::before($contentType, ';');

        if (! Str::startsWith($contentType, 'image/')) {
            Log::warning('Bank institution logo response was not an image.', [
                'slug' => $slug,
                'url' => $logoUrl,
                'content_type' => $contentType,
            ]);

            return null;
        }

        $extension = $this->extensionForContentType($contentType, $logoUrl);
        $path = "bank-institutions/{$slug}.{$extension}";

        Storage::disk('public')->put($path, $response->body());

        return $path;
    }

    public function resolveExistingLogoPath(string $slug, ?string $existingPath = null): ?string
    {
        if ($existingPath !== null && Storage::disk('public')->exists($existingPath)) {
            return $existingPath;
        }

        foreach (self::LOGO_EXTENSIONS as $extension) {
            $path = "bank-institutions/{$slug}.{$extension}";

            if (Storage::disk('public')->exists($path)) {
                return $path;
            }
        }

        return null;
    }

    private function extensionForContentType(string $contentType, string $logoUrl): string
    {
        return match ($contentType) {
            'image/png' => 'png',
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/svg+xml' => 'svg',
            'image/webp' => 'webp',
            'image/x-icon', 'image/vnd.microsoft.icon' => 'ico',
            default => Str::afterLast(Str::before(parse_url($logoUrl, PHP_URL_PATH) ?? '', '?'), '.') ?: 'png',
        };
    }
}
