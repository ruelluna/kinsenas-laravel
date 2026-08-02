import { Head, Link, usePage } from '@inertiajs/react';
import Heading from '@/components/heading';
import { PublicBetaAlert } from '@/components/public-beta-alert';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { BETA_PRICING_COMING_SOON_LABEL } from '@/lib/beta-copy';
import { formatMoneyFromCents } from '@/lib/format-money';
import type { SharedData } from '@/types';

type Props = {
    team: {
        id: number;
        name: string;
        slug: string;
        isPersonal: boolean;
    };
    canManageBilling: boolean;
    subscription: {
        status: string;
        statusLabel: string;
        trialEndsAt: string | null;
        currentPeriodEndsAt: string | null;
        planName: string | null;
    } | null;
    plans: Array<{
        id: string;
        name: string;
        slug: string;
        trialDays: number;
        prices: Array<{ id: string; interval: string; intervalLabel: string; amount: number; currency: string }>;
    }>;
    paymentMethod: {
        label: string;
        instructions: string | null;
        qrImageUrl: string | null;
    } | null;
};

export default function BillingSettings({ team, canManageBilling, subscription, plans, paymentMethod }: Props) {
    const page = usePage<SharedData & { flash?: { error?: string } }>();
    const sharedSubscription = page.props.subscription;
    const openBeta = page.props.openBeta;
    const hasAccess = sharedSubscription?.hasAccess ?? true;
    const flashError = page.props.flash?.error;
    const isLockedOut = !hasAccess && !openBeta.isActive;

    return (
        <>
            <Head title="Billing" />
            <Heading
                variant="small"
                title="Billing"
                description={
                    openBeta.isActive
                        ? 'Public beta is free. Paid plans are not available yet.'
                        : `Manage subscription and payments for ${team.name}.`
                }
            />

            {openBeta.isActive && (
                <PublicBetaAlert className="mt-6">
                    <p>
                        {team.name} has full access to the core savings planner at no cost. We are not
                        collecting payments during beta.
                    </p>
                    <p>
                        <Link href="/settings/feedback" className="font-medium underline underline-offset-4">
                            Send beta feedback
                        </Link>{' '}
                        anytime — it helps shape what we ship next.
                    </p>
                </PublicBetaAlert>
            )}

            {isLockedOut && (
                <Alert variant="destructive" className="mt-6">
                    <AlertTitle>Subscription required</AlertTitle>
                    <AlertDescription>
                        {flashError ??
                            `${team.name} requires an active subscription. Subscribe below to restore access.`}
                    </AlertDescription>
                </Alert>
            )}

            {!openBeta.isActive &&
                !isLockedOut &&
                sharedSubscription?.status === 'trialing' &&
                sharedSubscription.daysRemaining !== null && (
                    <Alert variant="info" className="mt-6">
                        <AlertTitle>Free trial active</AlertTitle>
                        <AlertDescription>
                            {sharedSubscription.daysRemaining === 0
                                ? `Your trial for ${team.name} ends today. Subscribe to keep access after it expires.`
                                : `${sharedSubscription.daysRemaining} day${sharedSubscription.daysRemaining === 1 ? '' : 's'} left in the free trial for ${team.name}.`}
                        </AlertDescription>
                    </Alert>
                )}

            {!openBeta.isActive && !canManageBilling && (
                <Alert variant="warning" className="mt-6">
                    <AlertTitle>Owner billing only</AlertTitle>
                    <AlertDescription>
                        Only the team owner can submit payment for {team.name}. Contact the owner to subscribe.
                    </AlertDescription>
                </Alert>
            )}

            {subscription && (
                <div className="mt-6 rounded-lg border p-4 text-sm">
                    <p><span className="font-medium">Team:</span> {team.name}</p>
                    <p><span className="font-medium">Status:</span> {subscription.statusLabel}</p>
                    {!openBeta.isActive && (
                        <p><span className="font-medium">Plan:</span> {subscription.planName ?? '—'}</p>
                    )}
                    {subscription.trialEndsAt && !openBeta.isActive && (
                        <p><span className="font-medium">Trial ends:</span> {new Date(subscription.trialEndsAt).toLocaleDateString()}</p>
                    )}
                </div>
            )}

            {openBeta.isActive && plans.length > 0 && (
                <div className="mt-8 space-y-4">
                    <div>
                        <h3 className="font-medium">Pricing</h3>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Paid plans are not open yet. Beta access stays free.
                        </p>
                    </div>
                    {plans.map((plan) => (
                        <div key={plan.id} className="rounded-lg border p-4">
                            <div className="flex flex-wrap items-center justify-between gap-2">
                                <h4 className="font-medium">{plan.name}</h4>
                                <span className="rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium text-muted-foreground">
                                    {BETA_PRICING_COMING_SOON_LABEL}
                                </span>
                            </div>
                            {plan.prices.length > 0 && (
                                <ul className="mt-3 space-y-1 text-sm text-muted-foreground">
                                    {plan.prices.map((price) => (
                                        <li key={price.id}>
                                            {price.intervalLabel} — {BETA_PRICING_COMING_SOON_LABEL}
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </div>
                    ))}
                </div>
            )}

            {!openBeta.isActive && paymentMethod && canManageBilling && (
                <div className="mt-6 rounded-lg border p-4">
                    <h3 className="font-medium">{paymentMethod.label}</h3>
                    {paymentMethod.qrImageUrl && (
                        <img src={paymentMethod.qrImageUrl} alt="Payment QR" className="mt-4 max-w-xs rounded border" />
                    )}
                    {paymentMethod.instructions && (
                        <p className="mt-3 text-sm text-muted-foreground">{paymentMethod.instructions}</p>
                    )}
                </div>
            )}

            {!openBeta.isActive && canManageBilling && (
                <div className="mt-8 space-y-4">
                    {plans.map((plan) => (
                        <div key={plan.id} className="rounded-lg border p-4">
                            <h3 className="font-medium">{plan.name}</h3>
                            <div className="mt-3 flex flex-wrap gap-2">
                                {plan.prices.map((price) => (
                                    <Button key={price.id} asChild variant="outline" size="sm">
                                        <Link href={`/billing/pay?plan_price_id=${price.id}`}>
                                            {price.intervalLabel} — {formatMoneyFromCents(price.amount)}
                                        </Link>
                                    </Button>
                                ))}
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </>
    );
}
