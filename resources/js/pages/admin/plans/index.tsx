import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';

type Props = {
    plans: Array<{
        id: string;
        name: string;
        slug: string;
        trialDays: number;
        isActive: boolean;
        prices: Array<{ id: string; interval: string; amount: number; currency: string; isActive: boolean }>;
    }>;
};

export default function AdminPlansIndex({ plans }: Props) {
    return (
        <>
            <Head title="Admin — Plans" />
            <Heading variant="small" title="Subscription plans" description="Platform admin only." />
            <ul className="mt-6 space-y-3">
                {plans.map((plan) => (
                    <li key={plan.id} className="rounded-lg border p-4 text-sm">
                        <p className="font-medium">{plan.name} ({plan.slug})</p>
                        <p className="text-muted-foreground">Trial: {plan.trialDays} days</p>
                    </li>
                ))}
            </ul>
        </>
    );
}
