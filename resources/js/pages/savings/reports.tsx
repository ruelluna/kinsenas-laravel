import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import FundUtilizationChart from '@/components/charts/fund-utilization-chart';
import IncomeVsSpendingChart from '@/components/charts/income-vs-spending-chart';
import SpendingByFundChart from '@/components/charts/spending-by-fund-chart';
import SpendingTrendChart from '@/components/charts/spending-trend-chart';
import TopRecipientsChart from '@/components/charts/top-recipients-chart';
import Heading from '@/components/heading';
import {
    MobileMetricCard,
    MobileMetricCardList,
} from '@/components/mobile/mobile-metric-card';
import { BankLogo } from '@/components/savings/bank-select';
import FundBankBadge from '@/components/savings/fund-bank-badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatMoney } from '@/lib/format-money';
import type { SharedData } from '@/types';
import type { FundGraphData, ReportTotals } from '@/types/savings';

type Props = {
    totals: ReportTotals;
    graphs: FundGraphData;
};

export default function SavingsReports({ totals, graphs }: Props) {
    const { currentTeam } = usePage<SharedData>().props;
    const teamSlug = currentTeam?.slug ?? '';
    const [from, setFrom] = useState(graphs.range.from ?? '');
    const [to, setTo] = useState(graphs.range.to ?? '');

    function applyDateRange() {
        router.get(
            `/${teamSlug}/savings/reports`,
            {
                from: from || undefined,
                to: to || undefined,
            },
            { preserveState: true, preserveScroll: true },
        );
    }

    function resetDateRange() {
        router.get(`/${teamSlug}/savings/reports`, {}, {
            preserveState: true,
            preserveScroll: true,
        });
    }

    return (
        <>
            <Head title="Reports" />
            <Heading
                variant="small"
                title="Reports"
                description="Fund health and bank balances (decrypted in your session only)."
            />

            <div
                className="mt-6 flex flex-col gap-3 rounded-lg border p-4 sm:flex-row sm:items-end"
                data-test="reports-date-filter"
            >
                <div className="grid flex-1 gap-3 sm:grid-cols-2">
                    <div className="space-y-2">
                        <Label htmlFor="reports-from">From</Label>
                        <Input
                            id="reports-from"
                            type="date"
                            value={from}
                            onChange={(event) => setFrom(event.target.value)}
                            data-test="reports-date-from"
                        />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="reports-to">To</Label>
                        <Input
                            id="reports-to"
                            type="date"
                            value={to}
                            onChange={(event) => setTo(event.target.value)}
                            data-test="reports-date-to"
                        />
                    </div>
                </div>
                <div className="flex gap-2">
                    <Button
                        type="button"
                        onClick={applyDateRange}
                        data-test="reports-date-apply"
                    >
                        Apply
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={resetDateRange}
                    >
                        Reset
                    </Button>
                </div>
            </div>

            <div className="mt-6 grid gap-6">
                <FundUtilizationChart data={graphs.fund_utilization} />
                <div className="grid gap-6 xl:grid-cols-2">
                    <SpendingTrendChart data={graphs.spending_over_time} />
                    <IncomeVsSpendingChart data={graphs.income_vs_spending} />
                </div>
                <div className="grid gap-6 xl:grid-cols-2">
                    <SpendingByFundChart data={graphs.spending_by_fund} />
                    <TopRecipientsChart data={graphs.top_recipients} />
                </div>
            </div>

            <div className="mt-8">
                <h3 className="font-medium">Fund health</h3>

                <div className="mt-3 md:hidden">
                    {totals.fund_health.length === 0 ? (
                        <p className="rounded-lg border border-dashed px-4 py-6 text-center text-sm text-muted-foreground">
                            Add income and record transfers or spending to see
                            fund health.
                        </p>
                    ) : (
                        <MobileMetricCardList>
                            {totals.fund_health.map((row) => (
                                <MobileMetricCard
                                    key={row.category_id}
                                    title={row.category_name}
                                    trailing={
                                        row.bank_id &&
                                        row.bank_display_name ? (
                                            <FundBankBadge
                                                bankDisplayName={
                                                    row.bank_display_name
                                                }
                                                bankLogoUrl={row.bank_logo_url}
                                                layout="inline"
                                            />
                                        ) : undefined
                                    }
                                    rows={[
                                        {
                                            label: 'Allocated',
                                            value: formatMoney(row.allocated),
                                        },
                                        {
                                            label: 'Transferred',
                                            value: formatMoney(row.transferred),
                                        },
                                        {
                                            label: 'Spent',
                                            value: formatMoney(row.spent),
                                        },
                                        {
                                            label: 'Remaining',
                                            value: formatMoney(row.remaining),
                                            strong: true,
                                        },
                                        {
                                            label: 'Used',
                                            value: `${row.percent_used}%`,
                                        },
                                    ]}
                                />
                            ))}
                        </MobileMetricCardList>
                    )}
                </div>

                <div className="mt-3 hidden overflow-x-auto rounded-lg border md:block">
                    <table className="w-full min-w-[640px] text-sm">
                        <thead>
                            <tr className="border-b bg-muted/50 text-left">
                                <th className="px-4 py-3 font-medium">
                                    Fund bucket
                                </th>
                                <th className="px-4 py-3 text-right font-medium">
                                    Allocated
                                </th>
                                <th className="px-4 py-3 text-right font-medium">
                                    Transferred
                                </th>
                                <th className="px-4 py-3 text-right font-medium">
                                    Spent
                                </th>
                                <th className="px-4 py-3 text-right font-medium">
                                    Remaining
                                </th>
                                <th className="px-4 py-3 text-right font-medium">
                                    Used
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {totals.fund_health.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={6}
                                        className="px-4 py-6 text-center text-muted-foreground"
                                    >
                                        Add income and record transfers or
                                        spending to see fund health.
                                    </td>
                                </tr>
                            ) : (
                                totals.fund_health.map((row) => (
                                    <tr
                                        key={row.category_id}
                                        className="border-b last:border-b-0"
                                    >
                                        <td className="px-4 py-3">
                                            <div className="flex items-start justify-between gap-3">
                                                <span>{row.category_name}</span>
                                                {row.bank_id &&
                                                    row.bank_display_name && (
                                                        <FundBankBadge
                                                            bankDisplayName={
                                                                row.bank_display_name
                                                            }
                                                            bankLogoUrl={
                                                                row.bank_logo_url
                                                            }
                                                            layout="inline"
                                                        />
                                                    )}
                                            </div>
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            {formatMoney(row.allocated)}
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            {formatMoney(row.transferred)}
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            {formatMoney(row.spent)}
                                        </td>
                                        <td className="px-4 py-3 text-right font-medium">
                                            {formatMoney(row.remaining)}
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            {row.percent_used}%
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            <div className="mt-8 grid gap-4 md:grid-cols-2 md:gap-6">
                <div className="rounded-lg border p-4">
                    <h3 className="font-medium">By bank</h3>
                    {totals.by_bank.length === 0 ? (
                        <p className="mt-3 text-sm text-muted-foreground">
                            No bank balances recorded yet.
                        </p>
                    ) : (
                        <ul className="mt-3 space-y-4 text-sm">
                            {totals.by_bank.map((row) => (
                                <li key={row.bank_id}>
                                    <div className="flex items-center justify-between gap-3">
                                        <div className="flex min-w-0 items-center gap-2">
                                            <BankLogo
                                                logoUrl={row.logo_url}
                                                name={row.bank_name}
                                            />
                                            <span className="truncate font-medium">
                                                {row.bank_name}
                                            </span>
                                        </div>
                                        <span className="shrink-0 tabular-nums">
                                            {formatMoney(row.total)}
                                        </span>
                                    </div>
                                    {row.by_category.length > 0 && (
                                        <ul className="mt-2 space-y-1 border-l pl-4 text-muted-foreground">
                                            {row.by_category.map((category) => (
                                                <li
                                                    key={category.category_id}
                                                    className="flex justify-between gap-2 text-xs sm:text-sm"
                                                >
                                                    <span className="min-w-0 truncate">
                                                        {category.category_name}
                                                    </span>
                                                    <span className="shrink-0 tabular-nums">
                                                        {formatMoney(
                                                            category.total,
                                                        )}
                                                    </span>
                                                </li>
                                            ))}
                                        </ul>
                                    )}
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
                <ReportSection
                    title="By recipient"
                    items={totals.by_recipient.map((row) => ({
                        label: row.recipient_name,
                        total: row.total,
                    }))}
                    emptyMessage="No recipient spending recorded yet."
                />
            </div>

            <p className="mt-6 flex flex-wrap gap-4 text-sm text-muted-foreground">
                <Link
                    href={`/${teamSlug}/savings/transfers`}
                    className="text-primary underline-offset-4 hover:underline"
                >
                    Record transfers →
                </Link>
                <Link
                    href={`/${teamSlug}/savings/spending`}
                    className="text-primary underline-offset-4 hover:underline"
                >
                    Record spending →
                </Link>
            </p>
        </>
    );
}

function ReportSection({
    title,
    items,
    emptyMessage,
}: {
    title: string;
    items: Array<{ label: string; total: string }>;
    emptyMessage: string;
}) {
    return (
        <div className="rounded-lg border p-4">
            <h3 className="font-medium">{title}</h3>
            <ul className="mt-3 space-y-2 text-sm">
                {items.length === 0 ? (
                    <li className="text-muted-foreground">{emptyMessage}</li>
                ) : (
                    items.map((item) => (
                        <li
                            key={item.label}
                            className="flex justify-between gap-2"
                        >
                            <span className="min-w-0 truncate">{item.label}</span>
                            <span className="shrink-0 font-medium tabular-nums">
                                {formatMoney(item.total)}
                            </span>
                        </li>
                    ))
                )}
            </ul>
        </div>
    );
}

SavingsReports.layout = (props: SharedData) => ({
    breadcrumbs: [
        {
            title: 'Reports',
            href: `/${props.currentTeam?.slug}/savings/reports`,
        },
    ],
});
