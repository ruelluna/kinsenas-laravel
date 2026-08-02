import { Form, Head, usePage } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import Heading from '@/components/heading';
import AddSpendingModal from '@/components/savings/add-spending-modal';
import EditSpendingModal from '@/components/savings/edit-spending-modal';
import FundBalanceGrid from '@/components/savings/fund-balance-grid';
import { Button } from '@/components/ui/button';
import { formatMoney } from '@/lib/format-money';
import type { SharedData } from '@/types';
import type { FundBalance, FundSpend } from '@/types/savings';

type Props = {
    plan: {
        id: string;
        name: string;
        hasLockedIncome: boolean;
        canDrawFromFunds: boolean;
        allowEditingSpends: boolean;
    };
    fundBalances: FundBalance[];
    defaultCategoryId: string | null;
    recipients: Array<{ id: string; name: string }>;
    categories: Array<{ id: string; name: string; bankId: string | null }>;
    spends: FundSpend[];
};

export default function SpendingIndex({
    plan,
    fundBalances,
    defaultCategoryId,
    recipients,
    categories,
    spends,
}: Props) {
    const { currentTeam } = usePage<SharedData>().props;
    const teamSlug = currentTeam?.slug ?? '';
    const [addModalOpen, setAddModalOpen] = useState(false);
    const [editModalOpen, setEditModalOpen] = useState(false);
    const [editingSpend, setEditingSpend] = useState<FundSpend | null>(null);
    const [presetCategoryId, setPresetCategoryId] = useState<string | null>(null);

    function openAddModal(categoryId: string | null = null) {
        setPresetCategoryId(categoryId);
        setAddModalOpen(true);
    }

    function openEditModal(spend: FundSpend) {
        setEditingSpend(spend);
        setEditModalOpen(true);
    }

    return (
        <>
            <Head title="Spending" />
            <div className="flex items-center justify-between">
                <Heading
                    variant="small"
                    title="Spending"
                    description={`Track spending from ${plan.name} fund balances.`}
                />
                {plan.canDrawFromFunds && (
                    <Button onClick={() => openAddModal()}>
                        <Plus /> Add spending
                    </Button>
                )}
            </div>

            {!plan.canDrawFromFunds && (
                <p className="mt-4 rounded-lg border border-dashed p-4 text-sm text-muted-foreground">
                    Lock at least one income period or add existing savings on your plan before
                    recording spending.
                </p>
            )}

            {plan.canDrawFromFunds && (
                <>
                    <AddSpendingModal
                        open={addModalOpen}
                        onOpenChange={setAddModalOpen}
                        presetCategoryId={presetCategoryId}
                        defaultCategoryId={defaultCategoryId}
                        categories={categories}
                        fundBalances={fundBalances}
                        recipients={recipients}
                    />
                    <EditSpendingModal
                        open={editModalOpen}
                        onOpenChange={setEditModalOpen}
                        spend={editingSpend}
                        categories={categories}
                        fundBalances={fundBalances}
                        recipients={recipients}
                    />
                </>
            )}

            {fundBalances.length > 0 && (
                <div className="mt-6">
                    <FundBalanceGrid
                        fundBalances={fundBalances}
                        variant="detailed"
                        hasLockedIncome={plan.canDrawFromFunds}
                        onSpendFrom={openAddModal}
                    />
                </div>
            )}

            <div className="mt-8">
                <h3 className="font-medium">Recent activity</h3>
                <div className="mt-3 space-y-3">
                    {spends.length === 0 ? (
                        <p className="text-sm text-muted-foreground">No spending recorded yet.</p>
                    ) : (
                        spends.map((spend) => (
                            <div
                                key={spend.id}
                                className="flex items-center justify-between gap-4 rounded-lg border p-3 text-sm"
                            >
                                <div>
                                    <p className="font-medium">
                                        {formatMoney(spend.amount)} · {spend.description}
                                    </p>
                                    <p className="text-muted-foreground">
                                        {spend.categoryName} · {spend.spentOn}
                                        {spend.bankName ? ` · ${spend.bankName}` : ''}
                                        {spend.recipientName ? ` → ${spend.recipientName}` : ''}
                                    </p>
                                    {spend.receiptImageUrl && (
                                        <a
                                            href={spend.receiptImageUrl}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="mt-2 inline-block"
                                        >
                                            <img
                                                src={spend.receiptImageUrl}
                                                alt={`Receipt for ${spend.description}`}
                                                className="size-16 rounded-md border object-cover"
                                            />
                                        </a>
                                    )}
                                </div>
                                <div className="flex shrink-0 items-center gap-2">
                                    {spend.status === 'pending' && (
                                        <Form
                                            action={`/${teamSlug}/savings/spending/${spend.id}/confirm`}
                                            method="post"
                                        >
                                            <Button type="submit" size="sm" variant="outline">
                                                Confirm
                                            </Button>
                                        </Form>
                                    )}
                                    {plan.allowEditingSpends && (
                                        <>
                                            <Button
                                                type="button"
                                                size="sm"
                                                variant="outline"
                                                onClick={() => openEditModal(spend)}
                                            >
                                                <Pencil className="size-4" />
                                                Edit
                                            </Button>
                                            <Form
                                                action={`/${teamSlug}/savings/spending/${spend.id}`}
                                                method="delete"
                                            >
                                                <Button type="submit" size="sm" variant="outline">
                                                    <Trash2 className="size-4" />
                                                    Delete
                                                </Button>
                                            </Form>
                                        </>
                                    )}
                                </div>
                            </div>
                        ))
                    )}
                </div>
            </div>
        </>
    );
}

SpendingIndex.layout = (props: SharedData) => ({
    breadcrumbs: [{ title: 'Spending', href: `/${props.currentTeam?.slug}/savings/spending` }],
});
