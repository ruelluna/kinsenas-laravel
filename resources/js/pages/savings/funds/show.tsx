import { Form, Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { useMemo, useState } from 'react';
import Heading from '@/components/heading';
import AddFundBalanceModal from '@/components/savings/add-fund-balance-modal';
import type { ExistingFundTarget } from '@/components/savings/add-fund-balance-modal';
import AddSpendingModal from '@/components/savings/add-spending-modal';
import AddTransferModal from '@/components/savings/add-transfer-modal';
import FundCardHeader from '@/components/savings/fund-card-header';
import { ResponsiveDataView } from '@/components/ui/responsive-data-view';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { categoryTitleWithAllocation } from '@/lib/category-allocation-label';
import { formatMoney } from '@/lib/format-money';
import { remainingTone } from '@/lib/fund-balance-tone';
import type { SharedData } from '@/types';
import type {
    CategoryBankMap,
    FundAddedEntryRow,
    FundBalance,
    FundCategoryAllocationRow,
    FundCategoryOption,
    FundCategoryTransferRow,
    FundSpend,
} from '@/types/savings';

type Props = {
    plan: {
        id: string;
        name: string;
        canDrawFromFunds: boolean;
        allowEditingSpends: boolean;
    };
    fundBalance: FundBalance;
    fundAddedEntries: FundAddedEntryRow[];
    allocations: FundCategoryAllocationRow[];
    transfers: FundCategoryTransferRow[];
    spends: FundSpend[];
    defaultCategoryId: string | null;
    recipients: Array<{ id: string; name: string }>;
    categories: FundCategoryOption[];
    categoryBankMap: CategoryBankMap;
    canTransfer: boolean;
};

function transferSummary(
    transfer: FundCategoryTransferRow,
    fundName: string,
): string {
    if (transfer.direction === 'out') {
        return `${fundName} → ${transfer.toCategoryName ?? 'Unknown'} · ${transfer.transferredOn}`;
    }

    return `${transfer.fromCategoryName ?? 'Unknown'} → ${fundName} · ${transfer.transferredOn}`;
}

export default function FundCategoryShow({
    plan,
    fundBalance,
    fundAddedEntries,
    allocations,
    transfers,
    spends,
    defaultCategoryId,
    recipients,
    categories,
    categoryBankMap,
    canTransfer,
}: Props) {
    const { currentTeam } = usePage<SharedData>().props;
    const teamSlug = currentTeam?.slug ?? '';
    const [fundModalOpen, setFundModalOpen] = useState(false);
    const [spendModalOpen, setSpendModalOpen] = useState(false);
    const [transferModalOpen, setTransferModalOpen] = useState(false);

    const pageTitle = categoryTitleWithAllocation(fundBalance.name, {
        allocationType: fundBalance.allocationType,
        percentage: fundBalance.percentage,
        deductionMode: fundBalance.deductionMode,
        deductionValue: fundBalance.deductionValue,
    });

    const existingFundTarget = useMemo<ExistingFundTarget>(
        () => ({
            categoryId: fundBalance.categoryId,
            name: fundBalance.name,
            openingBalance: fundBalance.openingBalance,
        }),
        [fundBalance],
    );

    const fundBalances = useMemo(() => [fundBalance], [fundBalance]);

    function renderTransferConfirm(transfer: FundCategoryTransferRow) {
        if (transfer.status !== 'pending') {
            return null;
        }

        return (
            <Form
                action={`/${teamSlug}/savings/transfers/${transfer.id}/confirm`}
                method="post"
            >
                <Button type="submit" size="sm" variant="outline">
                    Confirm
                </Button>
            </Form>
        );
    }

    return (
        <>
            <Head title={pageTitle} />

            <div className="mb-4">
                <Button variant="ghost" size="sm" asChild>
                    <Link href={`/${teamSlug}/savings/plan`}>
                        <ArrowLeft className="size-4" />
                        Back to Savings Plan
                    </Link>
                </Button>
            </div>

            <div
                className="rounded-lg border p-4"
                data-test="fund-detail-header"
            >
                <FundCardHeader
                    {...fundBalance}
                    showAllocationPercent
                />
                <div className="mt-4 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p className="text-xs text-muted-foreground">
                            Remaining
                        </p>
                        <p
                            className={`font-space text-2xl font-semibold ${remainingTone(fundBalance.percentUsed)}`}
                        >
                            {formatMoney(fundBalance.remaining)}
                        </p>
                    </div>
                    {fundBalance.percentUsed !== null && (
                        <Badge variant="outline">
                            {fundBalance.percentUsed}% used
                        </Badge>
                    )}
                </div>
            </div>

            <dl
                className="mt-4 grid gap-3 rounded-lg border p-4 font-space text-sm sm:grid-cols-2 lg:grid-cols-3"
                data-test="fund-detail-summary"
            >
                <div className="flex justify-between gap-2 sm:block">
                    <dt className="text-muted-foreground">Starting balance</dt>
                    <dd className="font-medium">
                        {formatMoney(fundBalance.openingBalance)}
                    </dd>
                </div>
                <div className="flex justify-between gap-2 sm:block">
                    <dt className="text-muted-foreground">Allocated</dt>
                    <dd className="font-medium">
                        {formatMoney(fundBalance.allocated)}
                    </dd>
                </div>
                <div className="flex justify-between gap-2 sm:block">
                    <dt className="text-muted-foreground">Transferred out</dt>
                    <dd className="font-medium">
                        {formatMoney(fundBalance.transferred)}
                    </dd>
                </div>
                <div className="flex justify-between gap-2 sm:block">
                    <dt className="text-muted-foreground">Received</dt>
                    <dd className="font-medium">
                        {formatMoney(fundBalance.received)}
                    </dd>
                </div>
                <div className="flex justify-between gap-2 sm:block">
                    <dt className="text-muted-foreground">Spent</dt>
                    <dd className="font-medium">
                        {formatMoney(fundBalance.spent)}
                    </dd>
                </div>
                <div className="flex justify-between gap-2 sm:block">
                    <dt className="text-muted-foreground">Remaining</dt>
                    <dd
                        className={`font-medium ${remainingTone(fundBalance.percentUsed)}`}
                    >
                        {formatMoney(fundBalance.remaining)}
                    </dd>
                </div>
            </dl>

            <div
                className="mt-6 flex flex-wrap gap-2"
                data-test="fund-detail-actions"
            >
                {fundBalance.canFund && (
                    <Button
                        type="button"
                        onClick={() => setFundModalOpen(true)}
                    >
                        Add Existing Fund
                    </Button>
                )}
                {plan.canDrawFromFunds && (
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() => setSpendModalOpen(true)}
                    >
                        Spend from {fundBalance.name}
                    </Button>
                )}
                {plan.canDrawFromFunds && canTransfer && (
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() => setTransferModalOpen(true)}
                    >
                        Transfer from {fundBalance.name}
                    </Button>
                )}
            </div>

            <AddFundBalanceModal
                open={fundModalOpen}
                onOpenChange={setFundModalOpen}
                target={existingFundTarget}
            />

            {plan.canDrawFromFunds && (
                <>
                    <AddSpendingModal
                        open={spendModalOpen}
                        onOpenChange={setSpendModalOpen}
                        presetCategoryId={fundBalance.categoryId}
                        defaultCategoryId={defaultCategoryId}
                        categories={categories}
                        fundBalances={fundBalances}
                        recipients={recipients}
                    />
                    {canTransfer && (
                        <AddTransferModal
                            open={transferModalOpen}
                            onOpenChange={setTransferModalOpen}
                            presetFromCategoryId={fundBalance.categoryId}
                            defaultCategoryId={defaultCategoryId}
                            categories={categories}
                            categoryBankMap={categoryBankMap}
                            fundBalances={fundBalances}
                        />
                    )}
                </>
            )}

            <section className="mt-8" data-test="fund-detail-starting-balance">
                <Heading
                    variant="small"
                    title="Starting balance history"
                    description="Each time you add existing savings to this fund bucket."
                />
                <div className="mt-3">
                    {fundAddedEntries.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            No starting balance entries recorded yet.
                        </p>
                    ) : (
                        <ResponsiveDataView
                            data={fundAddedEntries}
                            keyExtractor={(entry) => entry.id}
                            emptyMessage="No starting balance entries recorded yet."
                            columns={[
                                {
                                    key: 'addedOn',
                                    header: 'Date',
                                    render: (entry) => entry.addedOn,
                                },
                                {
                                    key: 'amount',
                                    header: 'Amount',
                                    render: (entry) => (
                                        <span className="font-medium">
                                            {formatMoney(entry.amount)}
                                        </span>
                                    ),
                                },
                            ]}
                        />
                    )}
                </div>
            </section>

            <section className="mt-8" data-test="fund-detail-allocations">
                <Heading
                    variant="small"
                    title="Income allocations"
                    description="Income locked to this fund bucket by period."
                />
                <div className="mt-3">
                    {allocations.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            No income allocations yet.
                        </p>
                    ) : (
                        <ResponsiveDataView
                            data={allocations}
                            keyExtractor={(row) => row.id}
                            emptyMessage="No income allocations yet."
                            columns={[
                                {
                                    key: 'period',
                                    header: 'Income period',
                                    render: (row) => (
                                        <Link
                                            href={`/${teamSlug}/savings/income/${row.periodId}`}
                                            className="text-primary underline-offset-4 hover:underline"
                                        >
                                            {row.periodName}
                                        </Link>
                                    ),
                                },
                                {
                                    key: 'periodStart',
                                    header: 'Period start',
                                    render: (row) => row.periodStart,
                                },
                                {
                                    key: 'amount',
                                    header: 'Amount',
                                    render: (row) => (
                                        <span className="font-medium">
                                            {formatMoney(row.amount)}
                                        </span>
                                    ),
                                },
                            ]}
                        />
                    )}
                </div>
            </section>

            <section className="mt-8" data-test="fund-detail-transfers">
                <Heading
                    variant="small"
                    title="Transfers"
                    description="Transfers in and out of this fund bucket."
                />
                <div className="mt-3">
                    {transfers.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            No transfers recorded yet.
                        </p>
                    ) : (
                        <ResponsiveDataView
                            data={transfers}
                            keyExtractor={(transfer) => transfer.id}
                            emptyMessage="No transfers recorded yet."
                            columns={[
                                {
                                    key: 'direction',
                                    header: 'Direction',
                                    render: (transfer) => (
                                        <Badge
                                            variant={
                                                transfer.direction === 'in'
                                                    ? 'secondary'
                                                    : 'outline'
                                            }
                                        >
                                            {transfer.direction === 'in'
                                                ? 'In'
                                                : 'Out'}
                                        </Badge>
                                    ),
                                },
                                {
                                    key: 'amount',
                                    header: 'Amount',
                                    render: (transfer) => (
                                        <span className="font-medium">
                                            {formatMoney(transfer.amount)}
                                        </span>
                                    ),
                                },
                                {
                                    key: 'description',
                                    header: 'Description',
                                    render: (transfer) =>
                                        transfer.description ?? '—',
                                },
                                {
                                    key: 'route',
                                    header: 'Route',
                                    render: (transfer) =>
                                        transferSummary(
                                            transfer,
                                            fundBalance.name,
                                        ),
                                },
                                {
                                    key: 'actions',
                                    header: 'Actions',
                                    render: (transfer) =>
                                        renderTransferConfirm(transfer),
                                },
                            ]}
                        />
                    )}
                </div>
            </section>

            <section className="mt-8" data-test="fund-detail-spending">
                <Heading
                    variant="small"
                    title="Spending"
                    description="Spending recorded from this fund bucket."
                />
                <div className="mt-3">
                    {spends.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            No spending recorded yet.
                        </p>
                    ) : (
                        <ResponsiveDataView
                            data={spends}
                            keyExtractor={(spend) => spend.id}
                            emptyMessage="No spending recorded yet."
                            columns={[
                                {
                                    key: 'amount',
                                    header: 'Amount',
                                    render: (spend) => (
                                        <span className="font-medium">
                                            {formatMoney(spend.amount)}
                                        </span>
                                    ),
                                },
                                {
                                    key: 'description',
                                    header: 'Description',
                                    render: (spend) =>
                                        spend.description ?? '—',
                                },
                                {
                                    key: 'details',
                                    header: 'Details',
                                    render: (spend) => (
                                        <span className="text-muted-foreground">
                                            {spend.spentOn}
                                            {spend.bankName
                                                ? ` · ${spend.bankName}`
                                                : ''}
                                            {spend.recipientName
                                                ? ` → ${spend.recipientName}`
                                                : ''}
                                        </span>
                                    ),
                                },
                            ]}
                        />
                    )}
                </div>
            </section>
        </>
    );
}

FundCategoryShow.layout = (props: Props & SharedData) => ({
    breadcrumbs: [
        {
            title: 'Savings Plan',
            href: `/${props.currentTeam?.slug}/savings/plan`,
        },
        {
            title: categoryTitleWithAllocation(props.fundBalance.name, {
                allocationType: props.fundBalance.allocationType,
                percentage: props.fundBalance.percentage,
                deductionMode: props.fundBalance.deductionMode,
                deductionValue: props.fundBalance.deductionValue,
            }),
            href: `/${props.currentTeam?.slug}/savings/funds/${props.fundBalance.categoryId}`,
        },
    ],
});
