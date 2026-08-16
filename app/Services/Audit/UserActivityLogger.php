<?php

namespace App\Services\Audit;

use App\Enums\UserActivityAction;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Spatie\Activitylog\Contracts\Activity;

class UserActivityLogger
{
    public function __construct(private ActivityPropertySanitizer $sanitizer) {}

    /**
     * @param  array<string, mixed>  $properties
     */
    public function log(
        UserActivityAction $action,
        string $description,
        ?User $causer = null,
        ?Model $subject = null,
        array $properties = [],
        ?Team $team = null,
    ): Activity {
        if (app()->environment(['local', 'testing']) && $this->sanitizer->containsForbiddenKeys($properties)) {
            throw new InvalidArgumentException('Activity log properties contain forbidden privacy keys.');
        }

        $logger = activity('kinsenas')
            ->event($action->value)
            ->withProperties($this->sanitizer->sanitize([
                ...$properties,
                'team_id' => $team?->id,
                'team_name' => $team?->name,
                'ip' => request()?->ip(),
                'user_agent' => Str::limit((string) request()?->userAgent(), 255),
            ]));

        if ($causer !== null) {
            $logger->causedBy($causer);
        }

        if ($subject !== null) {
            $logger->performedOn($subject);
        }

        return $logger->log($description);
    }
}
