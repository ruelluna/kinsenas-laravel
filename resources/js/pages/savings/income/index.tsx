import { Form, Head, Link, usePage } from '@inertiajs/react';
import { ChevronRight, Plus, Trash2 } from 'lucide-react';
import { useMemo, useState } from 'react';
import Heading from '@/components/heading';
import AddIncomeModal from '@/components/savings/add-income-modal';
import DeleteIncomeModal from '@/components/savings/delete-income-modal';
import { Button } from '@/components/ui/button';
import { useRegisterMobileNavAction } from '@/hooks/use-register-mobile-nav-action';
import { formatMoney } from '@/lib/format-money';
import type { SharedData } from '@/types';
import type {
    IncomeFundSummary,
    IncomePeriodTableRow,
    IncomePlanCategory,
} from '@/types/savings';

type Props = {
    plan: { id: string; name: string };
    planCategories: IncomePlanCategory[];
    periods: IncomePeriodTableRow[];
    fundSummary: IncomeFundSummary | null;
};

type ComputedTotals = {
    amount: string | null;
    categories: Record<string, string | null>;
};

type ComputedFundTotals = {
    spent: Record<string, string | null>;
    remaining: Record<string, string | null>;
    spentTotal: string | null;
    remainingTotal: string | null;
};

function formatPercentLabel(value: string): string {
    const numeric = parseFloat(value);

    if (!Number.isFinite(numeric)) {
        return '';
    }

    return `${Math.round(numeric)}%`;
}

function categoryHeaderSubtitle(category: IncomePlanCategory): string | null {
    if (category.allocationType === 'deduction') {
        if (
            category.deductionMode === 'percent_of_income' &&
            category.deductionValue
        ) {
            return formatPercentLabel(category.deductionValue);
        }

        return 'Custom';
    }

    return category.percentage !== null
        ? formatPercentLabel(category.percentage)
        : null;
}

function categoryInlineLabel(category: IncomePlanCategory): string {
    const subtitle = categoryHeaderSubtitle(category);

    return subtitle !== null ? `${category.name} · ${subtitle}` : category.name;
}

function CategoryHeader({ category }: { category: IncomePlanCategory }) {
    const subtitle = categoryHeaderSubtitle(category);

    return (
        <div className="flex flex-col items-end gap-0.5 leading-tight">
            <span>{category.name}</span>
            {subtitle !== null && (
                <span className="text-[11px] font-normal text-muted-foreground">
                    {subtitle}
                </span>
            )}
        </div>
    );
}

function sumMoneyAmounts(amounts: Array<string | null>): string | null {
    let total = 0;
    let hasValue = false;

    for (const amount of amounts) {
        if (amount === null || amount === '') {
            continue;
        }

        const value = parseFloat(amount);

        if (Number.isFinite(value)) {
            total += value;
            hasValue = true;
        }
    }

    return hasValue ? total.toFixed(2) : null;
}

function IncomePeriodMobileCard({
    period,
    planCategories,
    teamSlug,
    onDelete,
}: {
    period: IncomePeriodTableRow;
    planCategories: IncomePlanCategory[];
    teamSlug: string;
    onDelete: (period: IncomePeriodTableRow) => void;
}) {
    return (
        <div className="rounded-lg border p-3">
            <div className="flex items-start justify-between gap-3">
                <div className="min-w-0">
                    <Link
                        href={`/${teamSlug}/savings/income/${period.id}`}
                        className="font-medium text-primary underline-offset-4 hover:underline"
                    >
                        {period.name}
                    </Link>
                    <p className="mt-0.5 text-[11px] text-muted-foreground">
                        {period.periodStart}
                    </p>
                </div>
                <p className="shrink-0 text-sm font-semibold tabular-nums">
                    {formatMoney(period.amount)}
                </p>
            </div>

            <dl className="mt-3 space-y-1.5 border-t pt-3">
                {planCategories.map((category) => (
                    <div
                        key={category.id}
                        className="flex items-baseline justify-between gap-3 text-xs"
                    >
                        <dt className="min-w-0 text-muted-foreground">
                            {categoryInlineLabel(category)}
                        </dt>
                        <dd className="shrink-0 tabular-nums">
                            {formatMoney(
                                period.categoryAmounts[category.id] ?? null,
                            )}
                        </dd>
                    </div>
                ))}
            </dl>

            <div className="mt-3 flex items-center justify-between gap-2 border-t pt-3">
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    className="h-8 px-2 text-xs text-destructive hover:text-destructive"
                    onClick={() => onDelete(period)}
                >
                    <Trash2 className="size-3.5" />
                    Delete
                </Button>
                <Button
                    variant="ghost"
                    size="sm"
                    className="h-8 px-2 text-xs"
                    asChild
                >
                    <Link href={`/${teamSlug}/savings/income/${period.id}`}>
                        View detail
                        <ChevronRight className="size-3.5" />
                    </Link>
                </Button>
            </div>
        </div>
    );
}

function SummarySection({
    title,
    total,
    planCategories,
    categoryAmounts,
    muted = false,
}: {
    title: string;
    total: string | null;
    planCategories: IncomePlanCategory[];
    categoryAmounts: Record<string, string | null>;
    muted?: boolean;
}) {
    return (
        <div className={muted ? 'text-muted-foreground' : undefined}>
            <div className="flex items-baseline justify-between gap-3 font-medium">
                <span>{title}</span>
                <span className="tabular-nums">{formatMoney(total)}</span>
            </div>
            <dl className="mt-2 space-y-1 pl-3">
                {planCategories.map((category) => (
                    <div
                        key={category.id}
                        className="flex items-baseline justify-between gap-3 text-xs"
                    >
                        <dt>{categoryInlineLabel(category)}</dt>
                        <dd className="tabular-nums">
                            {formatMoney(categoryAmounts[category.id] ?? null)}
                        </dd>
                    </div>
                ))}
            </dl>
        </div>
    );
}

function IncomeMobileSummary({
    planCategories,
    totals,
    fundTotals,
}: {
    planCategories: IncomePlanCategory[];
    totals: ComputedTotals;
    fundTotals: ComputedFundTotals | null;
}) {
    return (
        <div className="rounded-lg border p-3">
            <h3 className="text-sm font-medium">Plan summary</h3>
            <div className="mt-3 space-y-4 text-sm">
                {fundTotals !== null && (
                    <SummarySection
                        title="Total spent"
                        total={fundTotals.spentTotal}
                        planCategories={planCategories}
                        categoryAmounts={fundTotals.spent}
                        muted
                    />
                )}
                <SummarySection
                    title="Total"
                    total={totals.amount}
                    planCategories={planCategories}
                    categoryAmounts={totals.categories}
                />
                {fundTotals !== null && (
                    <SummarySection
                        title="Remaining"
                        total={fundTotals.remainingTotal}
                        planCategories={planCategories}
                        categoryAmounts={fundTotals.remaining}
                    />
                )}
            </div>
        </div>
    );
}

export default function IncomeIndex({
    plan,
    planCategories,
    periods,
    fundSummary,
}: Props) {
    const { currentTeam } = usePage<SharedData>().props;
    const teamSlug = currentTeam?.slug ?? '';
    const [addModalOpen, setAddModalOpen] = useState(false);
    const [deleteTarget, setDeleteTarget] =
        useState<IncomePeriodTableRow | null>(null);

    const totals = useMemo(() => {
        const amountTotal = sumMoneyAmounts(
            periods.map((period) => period.amount),
        );
        const categoryTotals = Object.fromEntries(
            planCategories.map((category) => [
                category.id,
                sumMoneyAmounts(
                    periods.map(
                        (period) => period.categoryAmounts[category.id] ?? null,
                    ),
                ),
            ]),
        );

        return { amount: amountTotal, categories: categoryTotals };
    }, [periods, planCategories]);

    const fundTotals = useMemo(() => {
        if (fundSummary === null) {
            return null;
        }

        const spent = Object.fromEntries(
            planCategories.map((category) => [
                category.id,
                fundSummary.categorySpent[category.id] ?? null,
            ]),
        );
        const remaining = Object.fromEntries(
            planCategories.map((category) => [
                category.id,
                fundSummary.categoryRemaining[category.id] ?? null,
            ]),
        );

        return {
            spent,
            remaining,
            spentTotal: sumMoneyAmounts(Object.values(spent)),
            remainingTotal: sumMoneyAmounts(Object.values(remaining)),
        };
    }, [fundSummary, planCategories]);

    const columnCount = 3 + planCategories.length + 1;

    const mobileNavAction = useMemo(
        () => ({
            label: 'Add income',
            ariaLabel: 'Add income',
            icon: Plus,
            onClick: () => setAddModalOpen(true),
        }),
        [],
    );

    useRegisterMobileNavAction(mobileNavAction);

    return (
        <>
            <Head title="Income" />
            <div className="flex items-center justify-between gap-3">
                <Heading
                    variant="small"
                    title="Income"
                    description={`Enter monthly income for ${plan.name}. Allocations apply immediately.`}
                />
                <Button
                    onClick={() => setAddModalOpen(true)}
                    className="hidden shrink-0 md:inline-flex"
                >
                    <Plus /> Add income
                </Button>
            </div>

            <AddIncomeModal
                open={addModalOpen}
                onOpenChange={setAddModalOpen}
            />

            {deleteTarget !== null && (
                <DeleteIncomeModal
                    periodId={deleteTarget.id}
                    periodName={deleteTarget.name}
                    teamSlug={teamSlug}
                    open={deleteTarget !== null}
                    onOpenChange={(open) => {
                        if (!open) {
                            setDeleteTarget(null);
                        }
                    }}
                />
            )}

            <div className="mt-8 md:hidden">
                {periods.length === 0 ? (
                    <div className="rounded-lg border px-4 py-6 text-center text-sm text-muted-foreground">
                        No income recorded yet. Add your first income period.
                    </div>
                ) : (
                    <div className="space-y-3">
                        {periods.map((period) => (
                            <IncomePeriodMobileCard
                                key={period.id}
                                period={period}
                                planCategories={planCategories}
                                teamSlug={teamSlug}
                                onDelete={setDeleteTarget}
                            />
                        ))}
                        <IncomeMobileSummary
                            planCategories={planCategories}
                            totals={totals}
                            fundTotals={fundTotals}
                        />
                    </div>
                )}
            </div>

            <div className="mt-8 hidden overflow-x-auto rounded-lg border md:block">
                <table className="w-full min-w-180 text-xs">
                    <thead>
                        <tr className="border-b bg-muted/50 text-left">
                            <th className="px-2 py-1.5 font-medium">Date</th>
                            <th className="px-2 py-1.5 font-medium">
                                Income name
                            </th>
                            <th className="px-2 py-1.5 text-right font-medium">
                                Amount
                            </th>
                            {planCategories.map((category) => (
                                <th
                                    key={category.id}
                                    className="px-2 py-1.5 text-right align-bottom font-medium"
                                >
                                    <CategoryHeader category={category} />
                                </th>
                            ))}
                            <th className="px-2 py-1.5 text-right font-medium">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {periods.length === 0 ? (
                            <tr>
                                <td
                                    colSpan={columnCount}
                                    className="px-2 py-4 text-center text-muted-foreground"
                                >
                                    No income recorded yet. Add your first
                                    income period.
                                </td>
                            </tr>
                        ) : (
                            periods.map((period) => (
                                <tr
                                    key={period.id}
                                    className="border-b last:border-b-0"
                                >
                                    <td className="px-2 py-1.5 whitespace-nowrap">
                                        <Link
                                            href={`/${teamSlug}/savings/income/${period.id}`}
                                            className="font-medium text-primary underline-offset-4 hover:underline"
                                        >
                                            {period.periodStart}
                                        </Link>
                                    </td>
                                    <td className="px-2 py-1.5">
                                        {period.name}
                                    </td>
                                    <td className="px-2 py-1.5 text-right font-medium tabular-nums">
                                        {formatMoney(period.amount)}
                                    </td>
                                    {planCategories.map((category) => (
                                        <td
                                            key={category.id}
                                            className="px-2 py-1.5 text-right tabular-nums"
                                        >
                                            {formatMoney(
                                                period.categoryAmounts[
                                                    category.id
                                                ] ?? null,
                                            )}
                                        </td>
                                    ))}
                                    <td className="px-2 py-1.5 text-right">
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            className="h-7 px-2 text-xs text-destructive hover:text-destructive"
                                            onClick={() =>
                                                setDeleteTarget(period)
                                            }
                                        >
                                            Delete
                                        </Button>
                                    </td>
                                </tr>
                            ))
                        )}
                    </tbody>
                    {periods.length > 0 && (
                        <tfoot>
                            {fundTotals !== null && (
                                <tr className="border-t bg-muted/20 font-medium">
                                    <td className="px-2 py-1.5" colSpan={2}>
                                        Total spent
                                    </td>
                                    <td className="px-2 py-1.5 text-right tabular-nums">
                                        {formatMoney(fundTotals.spentTotal)}
                                    </td>
                                    {planCategories.map((category) => (
                                        <td
                                            key={category.id}
                                            className="px-2 py-1.5 text-right text-muted-foreground tabular-nums"
                                        >
                                            {formatMoney(
                                                fundTotals.spent[category.id] ??
                                                    null,
                                            )}
                                        </td>
                                    ))}
                                    <td className="px-2 py-1.5" />
                                </tr>
                            )}
                            <tr className="bg-muted/30 font-medium">
                                <td className="px-2 py-1.5" colSpan={2}>
                                    Total
                                </td>
                                <td className="px-2 py-1.5 text-right tabular-nums">
                                    {formatMoney(totals.amount)}
                                </td>
                                {planCategories.map((category) => (
                                    <td
                                        key={category.id}
                                        className="px-2 py-1.5 text-right tabular-nums"
                                    >
                                        {formatMoney(
                                            totals.categories[category.id] ??
                                                null,
                                        )}
                                    </td>
                                ))}
                                <td className="px-2 py-1.5" />
                            </tr>
                            {fundTotals !== null && (
                                <tr className="bg-muted/20 font-medium">
                                    <td className="px-2 py-1.5" colSpan={2}>
                                        Remaining
                                    </td>
                                    <td className="px-2 py-1.5 text-right tabular-nums">
                                        {formatMoney(fundTotals.remainingTotal)}
                                    </td>
                                    {planCategories.map((category) => (
                                        <td
                                            key={category.id}
                                            className="px-2 py-1.5 text-right tabular-nums"
                                        >
                                            {formatMoney(
                                                fundTotals.remaining[
                                                    category.id
                                                ] ?? null,
                                            )}
                                        </td>
                                    ))}
                                    <td className="px-2 py-1.5" />
                                </tr>
                            )}
                        </tfoot>
                    )}
                </table>
            </div>
        </>
    );
}

IncomeIndex.layout = (props: SharedData) => ({
    breadcrumbs: [
        { title: 'Income', href: `/${props.currentTeam?.slug}/savings/income` },
    ],
});
