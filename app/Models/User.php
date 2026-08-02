<?php

namespace App\Models;

use App\Concerns\HasTeams;
use App\Enums\BetaApplicationStatus;
use App\Services\Billing\BetaApplicationService;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property int|null $current_team_id
 * @property bool $is_platform_admin
 * @property Carbon|null $beta_enrolled_at
 * @property BetaApplicationStatus|null $beta_application_status
 * @property Carbon|null $beta_approved_at
 * @property int|null $beta_approved_by
 * @property bool $beta_launch_discount_eligible
 * @property bool $marketing_emails_opt_in
 * @property Carbon|null $marketing_emails_opted_in_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Team|null $currentTeam
 * @property-read Collection<int, Team> $ownedTeams
 * @property-read Collection<int, Membership> $teamMemberships
 * @property-read Collection<int, Team> $teams
 */
#[Fillable(['name', 'email', 'password', 'current_team_id', 'is_platform_admin', 'beta_enrolled_at', 'beta_application_status', 'beta_approved_at', 'beta_approved_by', 'beta_launch_discount_eligible', 'marketing_emails_opt_in', 'marketing_emails_opted_in_at'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasTeams, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_platform_admin' => 'boolean',
            'beta_enrolled_at' => 'datetime',
            'beta_application_status' => BetaApplicationStatus::class,
            'beta_approved_at' => 'datetime',
            'beta_launch_discount_eligible' => 'boolean',
            'marketing_emails_opt_in' => 'boolean',
            'marketing_emails_opted_in_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function vault(): HasOne
    {
        return $this->hasOne(UserVault::class);
    }

    public function paymentSubmissions(): HasMany
    {
        return $this->hasMany(PaymentSubmission::class);
    }

    public function betaFeedbacks(): HasMany
    {
        return $this->hasMany(BetaFeedback::class);
    }

    public function canManageBilling(Team $team): bool
    {
        if ($this->isPlatformAdmin()) {
            return true;
        }

        return $this->ownsTeam($team);
    }

    public function isPlatformAdmin(): bool
    {
        return (bool) $this->is_platform_admin;
    }

    public function hasApprovedBetaAccess(): bool
    {
        return app(BetaApplicationService::class)->hasAppAccess($this);
    }
}
