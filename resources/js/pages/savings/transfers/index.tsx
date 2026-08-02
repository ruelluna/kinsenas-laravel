import { Form, Head, usePage } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useState } from 'react';
import Heading from '@/components/heading';
import AddTransferModal from '@/components/savings/add-transfer-modal';
import FundBalanceGrid from '@/components/savings/fund-balance-grid';
import { Button } from '@/components/ui/button';
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
        hasLockedIncome: boolean;
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

    return (
        <>
            <Head title="Transfers" />
            <div className="flex items-center justify-between">
                <Heading
                    variant="small"
                    title="Transfers"
                    description={`Move savings between fund buckets in ${plan.name}.`}
                />
                {plan.canDrawFromFunds && (
                    <Button onClick={() => openAddModal()}>
                        <Plus /> Add transfer
                    </Button>
                )}
            </div>

            {!plan.canDrawFromFunds && (
                <p className="mt-4 rounded-lg border border-dashed p-4 text-sm text-muted-foreground">
                    Lock at least one income period or add a current balance on
                    your savings plan before recording transfers.
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
                <div className="mt-3 space-y-3">
                    {transfers.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            No transfers recorded yet.
                        </p>
                    ) : (
                        transfers.map((transfer) => (
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
                                            {transfer.fromBankName ?? 'No bank'}{' '}
                                            → {transfer.toBankName ?? 'No bank'}
                                        </p>
                                    )}
                                </div>
                                {transfer.status === 'pending' && (
                                    <Form
                                        action={`/${teamSlug}/savings/transfers/${transfer.id}/confirm`}
                                        method="post"
                                    >
                                        <Button
                                            type="submit"
                                            size="sm"
                                            variant="outline"
                                        >
                                            Confirm
                                        </Button>
                                    </Form>
                                )}
                            </div>
                        ))
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
