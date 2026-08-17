<?php

namespace App\Models;

use App\Concerns\HasTeams;
use App\Enums\FinanceActivityTier;
use App\Enums\PlatformPermission;
use App\Enums\PlatformRole;
use App\Support\UserProfilePhoto;
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
use Laravel\Sanctum\HasApiTokens;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string|null $profile_photo_path
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property int|null $current_team_id
 * @property Carbon|null $beta_enrolled_at
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
#[Fillable(['name', 'email', 'password', 'profile_photo_path', 'current_team_id', 'beta_enrolled_at', 'beta_launch_discount_eligible', 'marketing_emails_opt_in', 'marketing_emails_opted_in_at', 'payday_day_of_month'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasPushSubscriptions, HasRoles, HasTeams, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable {
        HasTeams::teams insteadof HasRoles;
    }

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
            'beta_enrolled_at' => 'datetime',
            'beta_launch_discount_eligible' => 'boolean',
            'marketing_emails_opt_in' => 'boolean',
            'marketing_emails_opted_in_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'finance_activity_tier' => FinanceActivityTier::class,
            'last_finance_activity_at' => 'datetime',
        ];
    }

    public function profilePhotoUrl(): ?string
    {
        return UserProfilePhoto::url($this);
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

    public function notificationPreferences(): HasOne
    {
        return $this->hasOne(UserNotificationPreference::class);
    }

    protected static function booted(): void
    {
        static::created(function (User $user): void {
            $user->notificationPreferences()->create(UserNotificationPreference::defaultAttributes());

            if ($user->roles()->doesntExist()) {
                $user->assignRole(PlatformRole::User->value);
            }
        });
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
        return $this->hasRole(PlatformRole::PlatformAdmin->value);
    }

    public function isAuthor(): bool
    {
        return $this->hasRole(PlatformRole::Author->value);
    }

    public function hasAdminPanelAccess(): bool
    {
        return $this->can(PlatformPermission::ManagePlatform->value)
            || $this->can(PlatformPermission::ManageContent->value);
    }

    public function platformRole(): ?PlatformRole
    {
        $roleName = $this->getRoleNames()->first();

        if ($roleName === null) {
            return null;
        }

        return PlatformRole::tryFrom($roleName);
    }

    public function syncPlatformRole(PlatformRole $role): void
    {
        $this->syncRoles([$role->value]);
    }

    public function canManagePlatform(): bool
    {
        return $this->can(PlatformPermission::ManagePlatform->value);
    }

    public function canManageContent(): bool
    {
        return $this->can(PlatformPermission::ManageContent->value);
    }

    public function canManageAllContent(): bool
    {
        return $this->canManagePlatform();
    }

    public function canManageContentPost(ContentPost $post): bool
    {
        if ($this->canManagePlatform()) {
            return true;
        }

        return $this->canManageContent() && $post->author_id === $this->id;
    }

    public function isBetaParticipant(): bool
    {
        return $this->beta_enrolled_at !== null;
    }
}
