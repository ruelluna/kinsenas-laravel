import { Form, Head, usePage } from '@inertiajs/react';
import { ResponsiveDataView } from '@kinsenas/ui';
import { Plus } from 'lucide-react';
import { useMemo, useState } from 'react';
import Heading from '@/components/heading';
import AddTransferModal from '@/components/savings/add-transfer-modal';
import FundBalanceGrid from '@/components/savings/fund-balance-grid';
import { Button } from '@/components/ui/button';
import { useRegisterMobileNavAction } from '@/hooks/use-register-mobile-nav-action';
import { formatMoney } from '@/lib/format-money';
import type { SharedData } from '@/types';
import type {
    CategoryBankMap,
    FundBalance,
    FundTransfer,
} from '@/types/savings';

type CategoryOption = {
    id: string;
    name: string;
    bankId: string | null;
    bankName: string | null;
    bankLogoUrl: string | null;
};

type Props = {
    plan: {
        id: string;
        name: string;
        canDrawFromFunds: boolean;
    };
    fundBalances: FundBalance[];
    defaultCategoryId: string | null;
    categories: CategoryOption[];
    categoryBankMap: CategoryBankMap;
    transfers: FundTransfer[];
};

export default function TransfersIndex({
    plan,
    fundBalances,
    defaultCategoryId,
    categories,
    categoryBankMap,
    transfers,
}: Props) {
    const { currentTeam } = usePage<SharedData>().props;
    const teamSlug = currentTeam?.slug ?? '';
    const [addModalOpen, setAddModalOpen] = useState(false);
    const [presetFromCategoryId, setPresetFromCategoryId] = useState<
        string | null
    >(null);

    function openAddModal(fromCategoryId: string | null = null) {
        setPresetFromCategoryId(fromCategoryId);
        setAddModalOpen(true);
    }

    function renderTransferConfirm(transfer: FundTransfer) {
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

    function transferRouteSummary(transfer: FundTransfer) {
        return (
            <span className="text-muted-foreground">
                {transfer.fromCategoryName} → {transfer.toCategoryName} ·{' '}
                {transfer.transferredOn}
                {transfer.crossesBanks && (
                    <>
                        {' '}
                        · {transfer.fromBankName ?? 'No bank'} →{' '}
                        {transfer.toBankName ?? 'No bank'}
                    </>
                )}
            </span>
        );
    }

    const mobileNavAction = useMemo(
        () =>
            plan.canDrawFromFunds
                ? {
                      label: 'Add transfer',
                      ariaLabel: 'Add transfer',
                      icon: Plus,
                      onClick: () => openAddModal(),
                  }
                : null,
        [plan.canDrawFromFunds],
    );

    useRegisterMobileNavAction(mobileNavAction);

    return (
        <>
            <Head title="Transfers" />
            <div className="flex flex-wrap items-center justify-between gap-3">
                <Heading
                    variant="small"
                    title="Transfers"
                    description={`Move savings between fund buckets in ${plan.name}.`}
                />
                {plan.canDrawFromFunds && (
                    <Button
                        className="hidden shrink-0 md:inline-flex"
                        onClick={() => openAddModal()}
                    >
                        <Plus /> Add transfer
                    </Button>
                )}
            </div>

            {!plan.canDrawFromFunds && (
                <p className="mt-4 rounded-lg border border-dashed p-4 text-sm text-muted-foreground">
                    Add income or existing savings to your plan before
                    recording transfers.
                </p>
            )}

            {plan.canDrawFromFunds && (
                <AddTransferModal
                    open={addModalOpen}
                    onOpenChange={setAddModalOpen}
                    presetFromCategoryId={presetFromCategoryId}
                    defaultCategoryId={defaultCategoryId}
                    categories={categories}
                    categoryBankMap={categoryBankMap}
                    fundBalances={fundBalances}
                />
            )}

            {fundBalances.length > 0 && (
                <div className="mt-6">
                    <FundBalanceGrid
                        fundBalances={fundBalances}
                        variant="detailed"
                        showReceived
                        transferredLabel="Transferred out"
                        canDrawFromFunds={plan.canDrawFromFunds}
                        action={{
                            label: (balance) => `Transfer from ${balance.name}`,
                            onClick: openAddModal,
                        }}
                    />
                </div>
            )}

            <div className="mt-8">
                <h3 className="font-medium">Recent transfers</h3>
                <div className="mt-3">
                    {transfers.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            No transfers recorded yet.
                        </p>
                    ) : (
                        <>
                            <div className="md:hidden">
                                <ResponsiveDataView
                                    data={transfers}
                                    isCompact
                                    keyExtractor={(transfer) => transfer.id}
                                    emptyMessage="No transfers recorded yet."
                                    columns={[
                                        {
                                            key: 'amount',
                                            header: 'Amount',
                                            render: (transfer) => (
                                                <span className="font-medium">
                                                    {formatMoney(
                                                        transfer.amount,
                                                    )}
                                                </span>
                                            ),
                                        },
                                        {
                                            key: 'description',
                                            header: 'Description',
                                            render: (transfer) =>
                                                transfer.description,
                                        },
                                        {
                                            key: 'route',
                                            header: 'Route',
                                            render: (transfer) =>
                                                transferRouteSummary(transfer),
                                        },
                                        {
                                            key: 'actions',
                                            header: 'Actions',
                                            render: (transfer) =>
                                                renderTransferConfirm(transfer),
                                        },
                                    ]}
                                />
                            </div>
                            <div className="hidden space-y-3 md:block">
                                {transfers.map((transfer) => (
                                    <div
                                        key={transfer.id}
                                        className="flex items-center justify-between gap-4 rounded-lg border p-3 text-sm"
                                    >
                                        <div>
                                            <p className="font-medium">
                                                {formatMoney(transfer.amount)} ·{' '}
                                                {transfer.description}
                                            </p>
                                            <p className="text-muted-foreground">
                                                {transfer.fromCategoryName} →{' '}
                                                {transfer.toCategoryName} ·{' '}
                                                {transfer.transferredOn}
                                            </p>
                                            {transfer.crossesBanks && (
                                                <p className="text-xs text-muted-foreground">
                                                    {transfer.fromBankName ??
                                                        'No bank'}{' '}
                                                    →{' '}
                                                    {transfer.toBankName ??
                                                        'No bank'}
                                                </p>
                                            )}
                                        </div>
                                        {renderTransferConfirm(transfer)}
                                    </div>
                                ))}
                            </div>
                        </>
                    )}
                </div>
            </div>
        </>
    );
}

TransfersIndex.layout = (props: SharedData) => ({
    breadcrumbs: [
        {
            title: 'Transfers',
            href: `/${props.currentTeam?.slug}/savings/transfers`,
        },
    ],
});
