import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TeamInvitationAlert from '@/components/team-invitation-alert';
import TextLink from '@/components/text-link';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { BETA_FREE_MESSAGE } from '@/lib/beta-copy';
import { formatMoneyFromCents } from '@/lib/format-money';
import { login } from '@/routes';
import { store } from '@/routes/register';
import type { TeamInvitationContext, OpenBetaOffer, TrialOffer } from '@/types';

type Props = {
    passwordRules: string;
    teamInvitation?: TeamInvitationContext | null;
    trialOffer?: TrialOffer | null;
    openBetaOffer?: OpenBetaOffer | null;
    betaCode?: string | null;
    betaCodeLabel?: string | null;
};

export default function Register({
    passwordRules,
    teamInvitation,
    trialOffer,
    openBetaOffer,
    betaCode,
    betaCodeLabel,
}: Props) {
    return (
        <>
            <Head title="Register" />
            <Form
                {...store.form()}
                resetOnSuccess={['password', 'password_confirmation']}
                disableWhileProcessing
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        {teamInvitation && (
                            <TeamInvitationAlert
                                invitation={teamInvitation}
                                action="Register"
                            />
                        )}

                        {openBetaOffer && (
                            <Alert variant="brand">
                                <AlertTitle>
                                    Apply for public beta access
                                </AlertTitle>
                                <AlertDescription className="space-y-2">
                                    <p>
                                        Create a real Kinsenas account and apply
                                        for the public beta. After you verify
                                        your email
                                        {betaCodeLabel ? (
                                            <>
                                                {' '}
                                                with your event code for{' '}
                                                <span className="font-medium">
                                                    {betaCodeLabel}
                                                </span>
                                            </>
                                        ) : (
                                            <>
                                                {' '}
                                                and we approve your application
                                            </>
                                        )}
                                        , you can use the core savings planner
                                        at no cost.
                                    </p>
                                    <p>
                                        Have an event code? Enter it below for
                                        instant beta approval after email
                                        verification.
                                    </p>
                                    <p>{BETA_FREE_MESSAGE}</p>
                                    <p className="text-muted-foreground">
                                        Pricing: coming soon.
                                    </p>
                                </AlertDescription>
                            </Alert>
                        )}

                        {trialOffer && (
                            <Alert variant="guidance">
                                <AlertTitle>
                                    Start your {trialOffer.trialDays}-day free
                                    trial
                                </AlertTitle>
                                <AlertDescription className="space-y-2">
                                    <p>
                                        Create an account and start a{' '}
                                        <span className="font-medium">
                                            {trialOffer.trialDays}-day free
                                            trial
                                        </span>{' '}
                                        on your personal finance workspace (
                                        <span className="font-medium">
                                            {trialOffer.name}
                                        </span>
                                        ). You will not be charged until the
                                        trial ends.
                                    </p>
                                    {trialOffer.prices.length > 0 && (
                                        <p>
                                            After your trial:{' '}
                                            {trialOffer.prices
                                                .map(
                                                    (price) =>
                                                        `${price.intervalLabel} ${formatMoneyFromCents(price.amount)}`,
                                                )
                                                .join(' · ')}
                                        </p>
                                    )}
                                </AlertDescription>
                            </Alert>
                        )}

                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="name"
                                    name="name"
                                    placeholder="Full name"
                                />
                                <InputError
                                    message={errors.name}
                                    className="mt-2"
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">Email address</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    tabIndex={2}
                                    autoComplete="email"
                                    name="email"
                                    placeholder="email@example.com"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">Password</Label>
                                <PasswordInput
                                    id="password"
                                    required
                                    tabIndex={3}
                                    autoComplete="new-password"
                                    name="password"
                                    placeholder="Password"
                                    passwordrules={passwordRules}
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">
                                    Confirm password
                                </Label>
                                <PasswordInput
                                    id="password_confirmation"
                                    required
                                    tabIndex={4}
                                    autoComplete="new-password"
                                    name="password_confirmation"
                                    placeholder="Confirm password"
                                    passwordrules={passwordRules}
                                />
                                <InputError
                                    message={errors.password_confirmation}
                                />
                            </div>

                            {openBetaOffer && (
                                <div className="grid gap-2">
                                    <Label htmlFor="beta_code">
                                        Beta access code (optional)
                                    </Label>
                                    <Input
                                        id="beta_code"
                                        type="text"
                                        tabIndex={5}
                                        name="beta_code"
                                        defaultValue={betaCode ?? ''}
                                        placeholder="KINSENAS-MNL-2026"
                                        autoComplete="off"
                                    />
                                    <InputError message={errors.beta_code} />
                                </div>
                            )}

                            <div className="flex items-start gap-3">
                                <input
                                    type="checkbox"
                                    id="marketing_emails_opt_in"
                                    name="marketing_emails_opt_in"
                                    value="1"
                                    tabIndex={6}
                                    className="mt-1 size-4 shrink-0 rounded border border-input shadow-xs"
                                />
                                <div className="grid gap-1">
                                    <Label
                                        htmlFor="marketing_emails_opt_in"
                                        className="leading-snug font-normal"
                                    >
                                        Send me helpful emails from Kinsenas
                                    </Label>
                                    <p className="text-sm text-muted-foreground">
                                        Occasional tips, product updates, and
                                        beta news. Not promotional spam —
                                        unsubscribe anytime.
                                    </p>
                                </div>
                            </div>

                            <Button
                                type="submit"
                                className="mt-2 w-full"
                                tabIndex={7}
                                data-test="register-user-button"
                            >
                                {processing && <Spinner />}
                                {openBetaOffer
                                    ? 'Apply for beta access'
                                    : 'Create account'}
                            </Button>
                        </div>

                        <div className="text-center text-sm text-muted-foreground">
                            Already have an account?{' '}
                            <TextLink
                                href={
                                    teamInvitation
                                        ? login.url({
                                              query: {
                                                  invitation:
                                                      teamInvitation.code,
                                              },
                                          })
                                        : login()
                                }
                                data-test="team-invitation-login-link"
                                tabIndex={8}
                            >
                                Log in
                            </TextLink>
                        </div>
                    </>
                )}
            </Form>
        </>
    );
}

Register.layout = (props: Props) => ({
    title: 'Create an account',
    description: props.openBetaOffer
        ? 'Apply for the free public beta — real accounts, core savings planner, pricing coming soon'
        : props.trialOffer
          ? `Start your ${props.trialOffer.trialDays}-day free trial on your personal finance workspace`
          : 'Enter your details below to create your account',
});
