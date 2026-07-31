import { Form, Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { IncomeBreakdownRow, IncomePeriodSummary } from '@/types/savings';
import type { SharedData } from '@/types';

type Props = {
    plan: { id: string; name: string };
    period: IncomePeriodSummary;
    breakdown: IncomeBreakdownRow[];
};

export default function IncomeShow({ plan, period, breakdown }: Props) {
    const { currentTeam } = usePage<SharedData>().props;
    const teamSlug = currentTeam?.slug ?? '';

    return (
        <>
            <Head title={`Income — ${period.periodStart}`} />

            <div className="mb-6">
                <Button variant="ghost" size="sm" asChild>
                    <Link href={`/${teamSlug}/savings/income`}>
                        <ArrowLeft className="size-4" />
                        Back to income
                    </Link>
                </Button>
            </div>

            <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <Heading
                    variant="small"
                    title={period.periodStart}
                    description={`${plan.name} breakdown`}
                />
                <div className="flex items-center gap-2">
                    <Badge variant={period.isLocked ? 'default' : 'secondary'}>
                        {period.isLocked ? 'Locked' : 'Preview'}
                    </Badge>
                    {!period.isLocked ? (
                        <Form action={`/${teamSlug}/savings/income/${period.id}/lock`} method="post">
                            <Button type="submit" size="sm">
                                Lock
                            </Button>
                        </Form>
                    ) : (
                        <Form action={`/${teamSlug}/savings/income/${period.id}/unlock`} method="post">
                            <Button type="submit" size="sm" variant="outline">
                                Unlock
                            </Button>
                        </Form>
                    )}
                </div>
            </div>

            <div className="mt-6 overflow-hidden rounded-lg border">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="border-b bg-muted/50 text-left">
                            <th className="px-4 py-3 font-medium">Category</th>
                            <th className="px-4 py-3 font-medium text-right">%</th>
                            <th className="px-4 py-3 font-medium text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        {breakdown.length === 0 ? (
                            <tr>
                                <td colSpan={3} className="px-4 py-6 text-center text-muted-foreground">
                                    No income amount set for this period.
                                </td>
                            </tr>
                        ) : (
                            breakdown.map((row) => (
                                <tr key={row.categoryId} className="border-b last:border-b-0">
                                    <td className="px-4 py-3">{row.name}</td>
                                    <td className="px-4 py-3 text-right">{row.percentage}%</td>
                                    <td className="px-4 py-3 text-right font-medium">
                                        {row.amount !== null ? `₱${row.amount}` : '—'}
                                    </td>
                                </tr>
                            ))
                        )}
                    </tbody>
                    {breakdown.length > 0 && (
                        <tfoot>
                            <tr className="bg-muted/30 font-medium">
                                <td className="px-4 py-3">Total</td>
                                <td className="px-4 py-3 text-right">100%</td>
                                <td className="px-4 py-3 text-right">
                                    {period.amount !== null ? `₱${period.amount}` : '—'}
                                </td>
                            </tr>
                        </tfoot>
                    )}
                </table>
            </div>

            {!period.isLocked && breakdown.length > 0 && (
                <p className="mt-3 text-sm text-muted-foreground">
                    Preview only — allocations are saved when you lock this period.
                </p>
            )}
        </>
    );
}

IncomeShow.layout = (props: Props & SharedData) => ({
    breadcrumbs: [
        { title: 'Income', href: `/${props.currentTeam?.slug}/savings/income` },
        {
            title: props.period.periodStart,
            href: `/${props.currentTeam?.slug}/savings/income/${props.period.id}`,
        },
    ],
});
