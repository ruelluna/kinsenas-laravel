<?php

namespace App\Support\Marketing;

final class GhlTagCatalog
{
    public const KINSENAS_USER = 'kinsenas-user';

    public const KINSENAS_BETA = 'kinsenas-beta';

    public const KINSENAS_SURVEY = 'kinsenas-survey';

    public const REGISTERED = 'registered';

    public const EMAIL_VERIFIED = 'email-verified';

    public const BETA_PENDING = 'beta-pending';

    public const BETA_APPROVED = 'beta-approved';

    public const BETA_REJECTED = 'beta-rejected';

    public const BETA_LAUNCH_DISCOUNT_ELIGIBLE = 'beta-launch-discount-eligible';

    public const BANK_ADDED = 'bank-added';

    public const GOTYME_GOSAVE_SETUP = 'gotyme-gosave-setup';

    public const PLAN_CREATED = 'plan-created';

    public const ABUNDANT_PLAN_CHOSEN = 'abundant-plan-chosen';

    public const TRC_PLAN_CHOSEN = 'trc-plan-chosen';

    public const CUSTOM_PLAN_CHOSEN = 'custom-plan-chosen';

    public const CUSTOM_PLAN_SLUG = 'custom';

    public const FIRST_INCOME_ENTERED = 'first-income-entered';

    public const INCOME_LOCKED = 'income-locked';

    public const ACTIVATED_USER = 'activated-user';

    public const FIRST_TRANSFER = 'first-transfer';

    public const FIRST_SPEND = 'first-spend';

    public const VAULT_UNLOCKED = 'vault-unlocked';

    public const BETA_FEEDBACK = 'beta-feedback';

    public const PAYMENT_SUBMITTED = 'payment-submitted';

    public const SUBSCRIPTION_ACTIVE = 'subscription-active';

    public const TRIAL_ACTIVE = 'trial-active';

    public const SUBSCRIPTION_CANCELLED = 'subscription-cancelled';

    public const TEAM_INVITE_SENT = 'team-invite-sent';

    public const TEAM_MEMBER = 'team-member';

    public const TEAM_CREATED = 'team-created';

    /**
     * @return array{0: list<string>, 1: list<string>}
     */
    public static function betaEventTags(string $event): ?array
    {
        return match ($event) {
            'application_submitted' => [
                [self::KINSENAS_BETA, self::BETA_PENDING],
                [],
            ],
            'application_approved' => [
                [self::KINSENAS_BETA, self::BETA_APPROVED],
                [self::BETA_PENDING, self::BETA_REJECTED],
            ],
            'application_rejected' => [
                [self::KINSENAS_BETA, self::BETA_REJECTED],
                [self::BETA_PENDING, self::BETA_APPROVED],
            ],
            default => null,
        };
    }

    public static function institutionBankAddedTag(string $slug): string
    {
        return "{$slug}-bank-added";
    }

    public static function betaFeedbackCategoryTag(string $category): string
    {
        return 'beta-feedback-'.$category;
    }

    public static function planChosenTagForTemplateSlug(string $slug): ?string
    {
        return match ($slug) {
            'abundant-formula' => self::ABUNDANT_PLAN_CHOSEN,
            'trc-savings' => self::TRC_PLAN_CHOSEN,
            self::CUSTOM_PLAN_SLUG => self::CUSTOM_PLAN_CHOSEN,
            default => null,
        };
    }

    /**
     * @return list<string>
     */
    public static function allPlanChosenTags(): array
    {
        return [
            self::ABUNDANT_PLAN_CHOSEN,
            self::TRC_PLAN_CHOSEN,
            self::CUSTOM_PLAN_CHOSEN,
        ];
    }

    /**
     * @return list<string>
     */
    public static function siblingPlanChosenTags(string $chosenTag): array
    {
        return array_values(array_filter(
            self::allPlanChosenTags(),
            fn (string $tag) => $tag !== $chosenTag,
        ));
    }

    /**
     * @return list<string>
     */
    public static function allStaticTags(): array
    {
        return [
            self::KINSENAS_USER,
            self::KINSENAS_BETA,
            self::KINSENAS_SURVEY,
            self::REGISTERED,
            self::EMAIL_VERIFIED,
            self::BETA_PENDING,
            self::BETA_APPROVED,
            self::BETA_REJECTED,
            self::BETA_LAUNCH_DISCOUNT_ELIGIBLE,
            self::BANK_ADDED,
            self::GOTYME_GOSAVE_SETUP,
            self::PLAN_CREATED,
            self::ABUNDANT_PLAN_CHOSEN,
            self::TRC_PLAN_CHOSEN,
            self::CUSTOM_PLAN_CHOSEN,
            self::FIRST_INCOME_ENTERED,
            self::INCOME_LOCKED,
            self::ACTIVATED_USER,
            self::FIRST_TRANSFER,
            self::FIRST_SPEND,
            self::VAULT_UNLOCKED,
            self::BETA_FEEDBACK,
            self::PAYMENT_SUBMITTED,
            self::SUBSCRIPTION_ACTIVE,
            self::TRIAL_ACTIVE,
            self::SUBSCRIPTION_CANCELLED,
            self::TEAM_INVITE_SENT,
            self::TEAM_MEMBER,
            self::TEAM_CREATED,
        ];
    }
}
