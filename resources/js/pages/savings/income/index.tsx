import { Form, Head, usePage } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { IncomePeriod } from '@/types/savings';
import type { SharedData } from '@/types';

type Props = {
    plan: { id: string; name: string };
    periods: IncomePeriod[];
};

export default function IncomeIndex({ plan, periods }: Props) {
    const { currentTeam } = usePage<SharedData>().props;
    const teamSlug = currentTeam?.slug ?? '';

    return (
        <>
            <Head title="Income" />
            <Heading
                variant="small"
                title="Income"
                description={`Enter monthly income for ${plan.name}. Lock to enable transfers.`}
            />

            <Form
                action={`/${teamSlug}/savings/income`}
                method="post"
                className="mt-6 grid max-w-md gap-4 rounded-lg border p-4"
            >
                <div className="grid gap-2">
                    <Label htmlFor="period_start">Period start</Label>
                    <Input id="period_start" name="period_start" type="date" required />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="amount">Income amount (PHP)</Label>
                    <Input id="amount" name="amount" type="number" step="0.01" min="0.01" required />
                </div>
                <Button type="submit">Save income</Button>
            </Form>

            <div className="mt-8 space-y-4">
                {periods.map((period) => (
                    <div key={period.id} className="rounded-lg border p-4">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="font-medium">{period.periodStart}</p>
                                <p className="text-sm text-muted-foreground">
                                    Amount: {period.amount ?? '—'} {period.isLocked ? '(locked)' : ''}
                                </p>
                            </div>
                            {!period.isLocked ? (
                                <Form action={`/${teamSlug}/savings/income/${period.id}/lock`} method="post">
                                    <Button type="submit" size="sm">Lock</Button>
                                </Form>
                            ) : (
                                <Form action={`/${teamSlug}/savings/income/${period.id}/unlock`} method="post">
                                    <Button type="submit" size="sm" variant="outline">Unlock</Button>
                                </Form>
                            )}
                        </div>
                        {period.allocations.length > 0 && (
                            <ul className="mt-3 space-y-1 text-sm">
                                {period.allocations.map((a, i) => (
                                    <li key={i}>{a.categoryName}: {a.amount ?? '—'}</li>
                                ))}
                            </ul>
                        )}
                    </div>
                ))}
            </div>
        </>
    );
}

IncomeIndex.layout = (page: { props: SharedData }) => ({
    breadcrumbs: [
        { title: 'Income', href: `/${page.props.currentTeam?.slug}/savings/income` },
    ],
});
