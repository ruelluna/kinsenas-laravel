import { Form, Head, usePage } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useState } from 'react';
import AddTransferModal from '@/components/savings/add-transfer-modal';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatMoney } from '@/lib/format-money';
import type { CategoryBankMap, FundBalance, FundTransfer } from '@/types/savings';
import type { SharedData } from '@/types';

type CategoryOption = {
    id: string;
    name: string;
    bankId: string | null;
    bankName: string | null;
    bankLogoUrl: string | null;
};

type Props = {
    plan: { id: string; name: string; hasLockedIncome: boolean };
    fundBalances: FundBalance[];
    defaultCategoryId: string | null;
    categories: CategoryOption[];
    categoryBankMap: CategoryBankMap;
    transfers: FundTransfer[];
};

function remainingTone(percentUsed: number | null): string {
    if (percentUsed === null) {
        return 'text-muted-foreground';
    }

    if (percentUsed >= 90) {
        return 'text-destructive';
    }

    if (percentUsed >= 70) {
        return 'text-amber-600 dark:text-amber-400';
    }

    return 'text-emerald-600 dark:text-emerald-400';
}

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
    const [presetFromCategoryId, setPresetFromCategoryId] = useState<string | null>(null);

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
                    description={`Move savings between funds in ${plan.name}.`}
                />
                {plan.hasLockedIncome && (
                    <Button onClick={() => openAddModal()}>
                        <Plus /> Add transfer
                    </Button>
                )}
            </div>

            {!plan.hasLockedIncome && (
                <p className="mt-4 rounded-lg border border-dashed p-4 text-sm text-muted-foreground">
                    Lock at least one income period before recording transfers.
                </p>
            )}

            {plan.hasLockedIncome && (
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
                <div className="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    {fundBalances.map((balance) => (
                        <div
                            key={balance.categoryId}
                            className={`rounded-lg border p-4 ${balance.isDefault ? 'ring-2 ring-primary/20' : ''}`}
                        >
                            <div className="flex items-start justify-between gap-2">
                                <div>
                                    <p className="font-medium">{balance.name}</p>
                                    {balance.hint && (
                                        <p className="mt-0.5 text-xs text-muted-foreground">{balance.hint}</p>
                                    )}
                                </div>
                                {balance.isDefault && <Badge variant="secondary">Default</Badge>}
                            </div>
                            <dl className="mt-4 space-y-1 text-sm">
                                <div className="flex justify-between gap-2">
                                    <dt className="text-muted-foreground">Allocated</dt>
                                    <dd>{formatMoney(balance.allocated)}</dd>
                                </div>
                                <div className="flex justify-between gap-2">
                                    <dt className="text-muted-foreground">Transferred out</dt>
                                    <dd>{formatMoney(balance.transferred)}</dd>
                                </div>
                                <div className="flex justify-between gap-2">
                                    <dt className="text-muted-foreground">Received</dt>
                                    <dd>{formatMoney(balance.received)}</dd>
                                </div>
                                <div className="flex justify-between gap-2">
                                    <dt className="text-muted-foreground">Spent</dt>
                                    <dd>{formatMoney(balance.spent)}</dd>
                                </div>
                                <div className="flex justify-between gap-2 font-medium">
                                    <dt>Remaining</dt>
                                    <dd className={remainingTone(balance.percentUsed)}>
                                        {formatMoney(balance.remaining)}
                                    </dd>
                                </div>
                            </dl>
                            {plan.hasLockedIncome && (
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    className="mt-4 w-full"
                                    onClick={() => openAddModal(balance.categoryId)}
                                >
                                    Transfer from {balance.name}
                                </Button>
                            )}
                        </div>
                    ))}
                </div>
            )}

            <div className="mt-8">
                <h3 className="font-medium">Recent transfers</h3>
                <div className="mt-3 space-y-3">
                    {transfers.length === 0 ? (
                        <p className="text-sm text-muted-foreground">No transfers recorded yet.</p>
                    ) : (
                        transfers.map((transfer) => (
                            <div
                                key={transfer.id}
                                className="flex items-center justify-between gap-4 rounded-lg border p-3 text-sm"
                            >
                                <div>
                                    <p className="font-medium">
                                        {formatMoney(transfer.amount)} · {transfer.description}
                                    </p>
                                    <p className="text-muted-foreground">
                                        {transfer.fromCategoryName} → {transfer.toCategoryName} ·{' '}
                                        {transfer.transferredOn}
                                    </p>
                                    {transfer.crossesBanks && (
                                        <p className="text-xs text-muted-foreground">
                                            {transfer.fromBankName ?? 'No bank'} →{' '}
                                            {transfer.toBankName ?? 'No bank'}
                                        </p>
                                    )}
                                </div>
                                {transfer.status === 'pending' && (
                                    <Form
                                        action={`/${teamSlug}/savings/transfers/${transfer.id}/confirm`}
                                        method="post"
                                    >
                                        <Button type="submit" size="sm" variant="outline">
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
    breadcrumbs: [{ title: 'Transfers', href: `/${props.currentTeam?.slug}/savings/transfers` }],
});
