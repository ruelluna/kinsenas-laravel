import { Head, Link } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { formatMoneyFromCents } from '@/lib/format-money';
import type { AdminPlan } from '@/types/billing';

type Props = {
    plans: AdminPlan[];
};

export default function AdminPlansIndex({ plans }: Props) {
    return (
        <>
            <Head title="Admin — Plans" />
            <div className="flex items-center justify-between gap-4">
                <Heading
                    variant="small"
                    title="Subscription plans"
                    description="Platform admin only."
                />
                <Button asChild>
                    <Link href="/admin/plans/create">Create plan</Link>
                </Button>
            </div>
            <ul className="mt-6 space-y-3">
                {plans.map((plan) => (
                    <li key={plan.id} className="rounded-lg border p-4 text-sm">
                        <div className="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p className="font-medium">
                                    {plan.name} ({plan.slug})
                                    {!plan.isActive && (
                                        <span className="ml-2 text-muted-foreground">
                                            — inactive
                                        </span>
                                    )}
                                </p>
                                <p className="text-muted-foreground">
                                    Trial: {plan.trialDays} days
                                </p>
                                {plan.features.length > 0 && (
                                    <p className="text-muted-foreground">
                                        Features: {plan.features.join(', ')}
                                    </p>
                                )}
                                <ul className="mt-2 space-y-1 text-muted-foreground">
                                    {plan.prices.map((price) => (
                                        <li key={price.id ?? price.interval}>
                                            {price.interval}:{' '}
                                            {formatMoneyFromCents(price.amount)}
                                            {!price.isActive && ' (inactive)'}
                                        </li>
                                    ))}
                                </ul>
                            </div>
                            <Button variant="outline" size="sm" asChild>
                                <Link href={`/admin/plans/${plan.id}/edit`}>
                                    Edit
                                </Link>
                            </Button>
                        </div>
                    </li>
                ))}
            </ul>
        </>
    );
}
