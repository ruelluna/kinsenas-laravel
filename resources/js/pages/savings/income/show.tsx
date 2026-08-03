import { Form, Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { useMemo, useState } from 'react';
import Heading from '@/components/heading';
import DeleteIncomeModal from '@/components/savings/delete-income-modal';
import FundBankBadge from '@/components/savings/fund-bank-badge';
import {
    MobileMetricCard,
    MobileMetricCardList,
} from '@/components/mobile/mobile-metric-card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatMoney } from '@/lib/format-money';
import type { SharedData } from '@/types';
import type {
    FundBalance,
    IncomeBreakdownRow,
    IncomeCustomCategory,
    IncomePeriodSummary,
} from '@/types/savings';

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
        if (
            category.periodAmount === null ||
            category.periodAmount === '0.00'
        ) {
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
    const [deleteOpen, setDeleteOpen] = useState(false);

    const balanceByCategory = useMemo(
        () =>
            Object.fromEntries(
                fundBalances.map((balance) => [balance.categoryId, balance]),
            ),
        [fundBalances],
    );

    const [customAmounts, setCustomAmounts] = useState<Record<string, string>>(
        () =>
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
                if (
                    row.allocationType === 'deduction' ||
                    row.percentage === null
                ) {
                    return total;
                }

                const value = parseFloat(row.percentage);

                return total + (Number.isFinite(value) ? value : 0);
            }, 0),
        [breakdown],
    );

    const hasDeductions = breakdown.some(
        (row) => row.allocationType === 'deduction',
    );

    return (
        <>
            <Head title={`Income — ${period.name}`} />

            <div className="mb-6 max-md:hidden">
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
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    className="text-destructive hover:text-destructive"
                    onClick={() => setDeleteOpen(true)}
                >
                    Delete income
                </Button>
            </div>

            <DeleteIncomeModal
                periodId={period.id}
                periodName={period.name}
                teamSlug={teamSlug}
                open={deleteOpen}
                onOpenChange={setDeleteOpen}
            />

            {customCategories.length > 0 && (
                <Form
                    action={`/${teamSlug}/savings/income/${period.id}/custom-amounts`}
                    method="put"
                    className="mt-6 space-y-4 rounded-lg border p-4"
                >
                    <div>
                        <h3 className="font-medium">Custom fund buckets</h3>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Optional amounts for this income period. Saving
                            recalculates fund allocations. Clear a field to skip
                            the deduction — the fund bucket stays on your plan.
                        </p>
                    </div>

                    {customCategories.map((category, index) => (
                        <div
                            key={category.categoryId}
                            className="grid gap-2 sm:grid-cols-2"
                        >
                            <input
                                type="hidden"
                                name={`custom_amounts[${index}][category_id]`}
                                value={category.categoryId}
                            />
                            <div>
                                <Label
                                    htmlFor={`custom-amount-${category.categoryId}`}
                                >
                                    {category.name}
                                </Label>
                                <p className="text-xs text-muted-foreground">
                                    From{' '}
                                    {category.deductFromCategoryName ??
                                        'source fund bucket'}
                                    {category.planDefaultAmount
                                        ? ` · plan default ${formatMoney(category.planDefaultAmount)}`
                                        : ''}
                                </p>
                            </div>
                            <div>
                                <Label
                                    htmlFor={`custom-amount-${category.categoryId}`}
                                >
                                    Amount this period (₱)
                                </Label>
                                <Input
                                    id={`custom-amount-${category.categoryId}`}
                                    name={`custom_amounts[${index}][amount]`}
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    value={
                                        customAmounts[category.categoryId] ?? ''
                                    }
                                    onChange={(event) =>
                                        setCustomAmounts((current) => ({
                                            ...current,
                                            [category.categoryId]:
                                                event.target.value,
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

            <div className="mt-6">
                <div className="md:hidden">
                    {breakdown.length === 0 ? (
                        <p className="rounded-lg border border-dashed px-4 py-6 text-center text-sm text-muted-foreground">
                            No income amount set for this period.
                        </p>
                    ) : (
                        <MobileMetricCardList>
                            {breakdown.map((row) => {
                                const fundBalance =
                                    balanceByCategory[row.categoryId];

                                return (
                                    <MobileMetricCard
                                        key={row.categoryId}
                                        title={formatCategoryLabel(row)}
                                        trailing={
                                            fundBalance?.bankId &&
                                            fundBalance.bankDisplayName ? (
                                                <FundBankBadge
                                                    bankDisplayName={
                                                        fundBalance.bankDisplayName
                                                    }
                                                    bankLogoUrl={
                                                        fundBalance.bankLogoUrl
                                                    }
                                                    layout="inline"
                                                />
                                            ) : undefined
                                        }
                                        rows={[
                                            {
                                                label: 'Allocation',
                                                value: formatPercentage(row),
                                            },
                                            {
                                                label: 'Amount',
                                                value: formatMoney(row.amount),
                                                strong: true,
                                            },
                                            ...(fundBalances.length > 0
                                                ? [
                                                      {
                                                          label: 'Remaining',
                                                          value: formatMoney(
                                                              fundBalance?.remaining ??
                                                                  null,
                                                          ),
                                                      },
                                                  ]
                                                : []),
                                        ]}
                                    />
                                );
                            })}
                            <div className="rounded-lg border bg-muted/30 p-3 text-sm font-medium">
                                <div className="flex items-baseline justify-between gap-3">
                                    <span>Total</span>
                                    <span className="tabular-nums">
                                        {formatMoney(period.amount)}
                                    </span>
                                </div>
                                <p className="mt-1 text-xs font-normal text-muted-foreground">
                                    {hasDeductions
                                        ? `${percentageTotal.toFixed(2)}% + custom allocations`
                                        : '100% of income'}
                                </p>
                            </div>
                        </MobileMetricCardList>
                    )}
                </div>

                <div className="hidden overflow-hidden rounded-lg border md:block">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="border-b bg-muted/50 text-left">
                            <th className="px-4 py-3 font-medium">
                                Fund bucket
                            </th>
                            <th className="px-4 py-3 text-right font-medium">
                                Allocation
                            </th>
                            <th className="px-4 py-3 text-right font-medium">
                                Amount
                            </th>
                            {fundBalances.length > 0 && (
                                <th className="px-4 py-3 text-right font-medium">
                                    Remaining
                                </th>
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
                            breakdown.map((row) => {
                                const fundBalance =
                                    balanceByCategory[row.categoryId];

                                return (
                                    <tr
                                        key={row.categoryId}
                                        className="border-b last:border-b-0"
                                    >
                                        <td className="px-4 py-3">
                                            <div className="flex items-start justify-between gap-3">
                                                <span>
                                                    {formatCategoryLabel(row)}
                                                </span>
                                                {fundBalance?.bankId &&
                                                    fundBalance.bankDisplayName && (
                                                        <FundBankBadge
                                                            bankDisplayName={
                                                                fundBalance.bankDisplayName
                                                            }
                                                            bankLogoUrl={
                                                                fundBalance.bankLogoUrl
                                                            }
                                                            layout="inline"
                                                        />
                                                    )}
                                            </div>
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            {formatPercentage(row)}
                                        </td>
                                        <td className="px-4 py-3 text-right font-medium">
                                            {formatMoney(row.amount)}
                                        </td>
                                        {fundBalances.length > 0 && (
                                            <td className="px-4 py-3 text-right">
                                                {formatMoney(
                                                    fundBalance?.remaining ??
                                                        null,
                                                )}
                                            </td>
                                        )}
                                    </tr>
                                );
                            })
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
                                {fundBalances.length > 0 && (
                                    <td className="px-4 py-3" />
                                )}
                            </tr>
                        </tfoot>
                    )}
                </table>
                </div>
            </div>

            {fundBalances.length > 0 && (
                <p className="mt-3 text-sm text-muted-foreground">
                    Remaining balances reflect all income allocations minus
                    confirmed spending.{' '}
                    <Link
                        href={`/${teamSlug}/savings/spending`}
                        className="text-primary underline-offset-4 hover:underline"
                    >
                        Record spending →
                    </Link>
                </p>
            )}
        </>
    );
}

IncomeShow.layout = (props: Props & SharedData) => ({
    breadcrumbs: [
        { title: 'Income', href: `/${props.currentTeam?.slug}/savings/income` },
        {
            title: props.period.name,
            href: `/${props.currentTeam?.slug}/savings/income/${props.period.id}`,
        },
    ],
});
