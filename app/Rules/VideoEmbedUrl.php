<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class VideoEmbedUrl implements ValidationRule
{
    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value)) {
            $fail(__('The :attribute must be a valid video URL.'));

            return;
        }

        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            $fail(__('The :attribute must be a valid URL.'));

            return;
        }

        $host = parse_url($value, PHP_URL_HOST);

        if (! is_string($host)) {
            $fail(__('The :attribute must be a supported video embed URL.'));

            return;
        }

        $host = strtolower($host);

        $allowedHosts = [
            'www.youtube.com',
            'youtube.com',
            'youtu.be',
            'www.youtube-nocookie.com',
            'youtube-nocookie.com',
            'player.vimeo.com',
            'vimeo.com',
        ];

        if (! in_array($host, $allowedHosts, true)) {
            $fail(__('The :attribute must be a YouTube or Vimeo URL.'));
        }
    }
}
