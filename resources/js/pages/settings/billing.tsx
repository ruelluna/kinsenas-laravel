import { Head, Link } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';

type Props = {
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

export default function BillingSettings({ subscription, plans, paymentMethod }: Props) {
    return (
        <>
            <Head title="Billing" />
            <Heading variant="small" title="Billing" description="Manage your subscription and payments." />

            {subscription && (
                <div className="mt-6 rounded-lg border p-4 text-sm">
                    <p><span className="font-medium">Status:</span> {subscription.statusLabel}</p>
                    <p><span className="font-medium">Plan:</span> {subscription.planName ?? '—'}</p>
                    {subscription.trialEndsAt && (
                        <p><span className="font-medium">Trial ends:</span> {new Date(subscription.trialEndsAt).toLocaleDateString()}</p>
                    )}
                </div>
            )}

            {paymentMethod && (
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

            <div className="mt-8 space-y-4">
                {plans.map((plan) => (
                    <div key={plan.id} className="rounded-lg border p-4">
                        <h3 className="font-medium">{plan.name}</h3>
                        <div className="mt-3 flex flex-wrap gap-2">
                            {plan.prices.map((price) => (
                                <Button key={price.id} asChild variant="outline" size="sm">
                                    <Link href={`/billing/pay?plan_price_id=${price.id}`}>
                                        {price.intervalLabel} — ₱{(price.amount / 100).toFixed(2)}
                                    </Link>
                                </Button>
                            ))}
                        </div>
                    </div>
                ))}
            </div>
        </>
    );
}
