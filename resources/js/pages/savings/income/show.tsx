import { Form, Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { useMemo, useState } from 'react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatMoney } from '@/lib/format-money';
import type {
    FundBalance,
    IncomeBreakdownRow,
    IncomeCustomCategory,
    IncomePeriodSummary,
} from '@/types/savings';
import type { SharedData } from '@/types';

type Props = {
    plan: { id: string; name: string };
    period: IncomePeriodSummary;
    breakdown: IncomeBreakdownRow[];
    customCategories: IncomeCustomCategory[];
    fundBalances: FundBalance[];
};

function formatPercentage(row: IncomeBreakdownRow): string {
    if (row.allocationType === 'deduction') {
        if (row.deductionMode === 'percent_of_income' && row.deductionValue) {
            return `${row.deductionValue}% income`;
        }

        return 'Custom';
    }

    return row.percentage !== null ? `${row.percentage}%` : '—';
}

function formatCategoryLabel(row: IncomeBreakdownRow): string {
    if (row.deductionNote) {
        return `${row.name} (${row.deductionNote})`;
    }

    return row.name;
}

function customAmountInputValue(category: IncomeCustomCategory): string {
    if (category.hasPeriodOverride) {
        if (category.periodAmount === null || category.periodAmount === '0.00') {
            return '';
        }

        return category.periodAmount;
    }

    return category.planDefaultAmount ?? '';
}

export default function IncomeShow({
    plan,
    period,
    breakdown,
    customCategories,
    fundBalances,
}: Props) {
    const { currentTeam } = usePage<SharedData>().props;
    const teamSlug = currentTeam?.slug ?? '';

    const balanceByCategory = useMemo(
        () => Object.fromEntries(fundBalances.map((balance) => [balance.categoryId, balance])),
        [fundBalances],
    );

    const [customAmounts, setCustomAmounts] = useState<Record<string, string>>(() =>
        Object.fromEntries(
            customCategories.map((category) => [
                category.categoryId,
                customAmountInputValue(category),
            ]),
        ),
    );

    const percentageTotal = useMemo(
        () =>
            breakdown.reduce((total, row) => {
                if (row.allocationType === 'deduction' || row.percentage === null) {
                    return total;
                }

                const value = parseFloat(row.percentage);

                return total + (Number.isFinite(value) ? value : 0);
            }, 0),
        [breakdown],
    );

    const hasDeductions = breakdown.some((row) => row.allocationType === 'deduction');

    return (
        <>
            <Head title={`Income — ${period.name}`} />

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
                    title={period.name}
                    description={`${period.periodStart} · ${plan.name} breakdown`}
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

            {!period.isLocked && customCategories.length > 0 && (
                <Form
                    action={`/${teamSlug}/savings/income/${period.id}/custom-amounts`}
                    method="put"
                    className="mt-6 space-y-4 rounded-lg border p-4"
                >
                    <div>
                        <h3 className="font-medium">Custom categories</h3>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Optional amounts for this income period. Clear a field to skip the
                            deduction — the category stays on your plan.
                        </p>
                    </div>

                    {customCategories.map((category, index) => (
                        <div key={category.categoryId} className="grid gap-2 sm:grid-cols-2">
                            <input
                                type="hidden"
                                name={`custom_amounts[${index}][category_id]`}
                                value={category.categoryId}
                            />
                            <div>
                                <Label htmlFor={`custom-amount-${category.categoryId}`}>
                                    {category.name}
                                </Label>
                                <p className="text-xs text-muted-foreground">
                                    From {category.deductFromCategoryName ?? 'source category'}
                                    {category.planDefaultAmount
                                        ? ` · plan default ${formatMoney(category.planDefaultAmount)}`
                                        : ''}
                                </p>
                            </div>
                            <div>
                                <Label htmlFor={`custom-amount-${category.categoryId}`}>
                                    Amount this period (₱)
                                </Label>
                                <Input
                                    id={`custom-amount-${category.categoryId}`}
                                    name={`custom_amounts[${index}][amount]`}
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    value={customAmounts[category.categoryId] ?? ''}
                                    onChange={(event) =>
                                        setCustomAmounts((current) => ({
                                            ...current,
                                            [category.categoryId]: event.target.value,
                                        }))
                                    }
                                    placeholder="Leave blank for no deduction"
                                />
                            </div>
                        </div>
                    ))}

                    <Button type="submit" size="sm">
                        Save custom amounts
                    </Button>
                </Form>
            )}

            <div className="mt-6 overflow-hidden rounded-lg border">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="border-b bg-muted/50 text-left">
                            <th className="px-4 py-3 font-medium">Category</th>
                            <th className="px-4 py-3 font-medium text-right">Allocation</th>
                            <th className="px-4 py-3 font-medium text-right">Amount</th>
                            {fundBalances.length > 0 && (
                                <th className="px-4 py-3 font-medium text-right">Remaining</th>
                            )}
                        </tr>
                    </thead>
                    <tbody>
                        {breakdown.length === 0 ? (
                            <tr>
                                <td
                                    colSpan={fundBalances.length > 0 ? 4 : 3}
                                    className="px-4 py-6 text-center text-muted-foreground"
                                >
                                    No income amount set for this period.
                                </td>
                            </tr>
                        ) : (
                            breakdown.map((row) => (
                                <tr key={row.categoryId} className="border-b last:border-b-0">
                                    <td className="px-4 py-3">{formatCategoryLabel(row)}</td>
                                    <td className="px-4 py-3 text-right">{formatPercentage(row)}</td>
                                    <td className="px-4 py-3 text-right font-medium">
                                        {formatMoney(row.amount)}
                                    </td>
                                    {fundBalances.length > 0 && (
                                        <td className="px-4 py-3 text-right">
                                            {formatMoney(balanceByCategory[row.categoryId]?.remaining ?? null)}
                                        </td>
                                    )}
                                </tr>
                            ))
                        )}
                    </tbody>
                    {breakdown.length > 0 && (
                        <tfoot>
                            <tr className="bg-muted/30 font-medium">
                                <td className="px-4 py-3">Total</td>
                                <td className="px-4 py-3 text-right">
                                    {hasDeductions
                                        ? `${percentageTotal.toFixed(2)}% + custom`
                                        : '100%'}
                                </td>
                                <td className="px-4 py-3 text-right">
                                    {formatMoney(period.amount)}
                                </td>
                                {fundBalances.length > 0 && <td className="px-4 py-3" />}
                            </tr>
                        </tfoot>
                    )}
                </table>
            </div>

            {fundBalances.length > 0 && (
                <p className="mt-3 text-sm text-muted-foreground">
                    Remaining balances reflect all locked income minus confirmed spending.{' '}
                    <Link href={`/${teamSlug}/savings/spending`} className="text-primary underline-offset-4 hover:underline">
                        Record spending →
                    </Link>
                </p>
            )}

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
            title: period.name,
            href: `/${props.currentTeam?.slug}/savings/income/${props.period.id}`,
        },
    ],
});
