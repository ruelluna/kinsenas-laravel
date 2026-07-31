import { Form, Head, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import BankSelect, { banksForCategory } from '@/components/savings/bank-select';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatMoney } from '@/lib/format-money';
import type { BankOption, CategoryBankMap, FundBalance, FundTransfer } from '@/types/savings';
import type { SharedData } from '@/types';

type Props = {
    plan: { id: string; name: string; hasLockedIncome: boolean };
    fundBalances: FundBalance[];
    defaultCategoryId: string | null;
    banks: BankOption[];
    categories: Array<{ id: string; name: string; bankIds: string[] }>;
    categoryBankMap: CategoryBankMap;
    transfers: FundTransfer[];
};

function todayString(): string {
    return new Date().toISOString().slice(0, 10);
}

function remainingAfterAmount(remaining: string | null, amount: string): string | null {
    if (remaining === null || amount === '') {
        return remaining;
    }

    const parsed = parseFloat(amount);

    if (!Number.isFinite(parsed)) {
        return remaining;
    }

    return (parseFloat(remaining) - parsed).toFixed(2);
}

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
    banks,
    categories,
    categoryBankMap,
    transfers,
}: Props) {
    const { currentTeam, errors } = usePage<SharedData & { errors: Record<string, string> }>().props;
    const teamSlug = currentTeam?.slug ?? '';
    const [selectedCategoryId, setSelectedCategoryId] = useState(defaultCategoryId ?? categories[0]?.id ?? '');
    const [selectedBankId, setSelectedBankId] = useState('');
    const [amount, setAmount] = useState('');

    const selectedBalance = useMemo(
        () => fundBalances.find((balance) => balance.categoryId === selectedCategoryId) ?? null,
        [fundBalances, selectedCategoryId],
    );

    const availableBanks = useMemo(
        () => banksForCategory(banks, categoryBankMap, selectedCategoryId),
        [banks, categoryBankMap, selectedCategoryId],
    );

    const projectedRemaining = remainingAfterAmount(selectedBalance?.remaining ?? null, amount);

    return (
        <>
            <Head title="Transfers" />
            <Heading
                variant="small"
                title="Transfers"
                description={`Move allocated savings into bank accounts for ${plan.name}.`}
            />

            {!plan.hasLockedIncome && (
                <p className="mt-4 rounded-lg border border-dashed p-4 text-sm text-muted-foreground">
                    Lock at least one income period before recording transfers.
                </p>
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
                                    <dt className="text-muted-foreground">Transferred</dt>
                                    <dd>{formatMoney(balance.transferred)}</dd>
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
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                className="mt-4 w-full"
                                onClick={() => setSelectedCategoryId(balance.categoryId)}
                            >
                                Transfer to {balance.name}
                            </Button>
                        </div>
                    ))}
                </div>
            )}

            {plan.hasLockedIncome && (
                <Form
                    action={`/${teamSlug}/savings/transfers`}
                    method="post"
                    className="mt-8 max-w-lg space-y-4 rounded-lg border p-4"
                >
                    <div>
                        <h3 className="font-medium">Record transfer</h3>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Log money moved from income into a fund&apos;s bank account. Confirm when it arrives.
                        </p>
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="category_id">Fund</Label>
                        <select
                            id="category_id"
                            name="category_id"
                            className="border-input h-9 rounded-md border px-3 text-sm"
                            value={selectedCategoryId}
                            onChange={(event) => {
                                setSelectedCategoryId(event.target.value);
                                setSelectedBankId('');
                            }}
                            required
                        >
                            {categories.map((category) => (
                                <option key={category.id} value={category.id}>
                                    {category.name}
                                </option>
                            ))}
                        </select>
                        <InputError message={errors.category_id} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="bank_id">Bank</Label>
                        <BankSelect
                            id="bank_id"
                            banks={banks}
                            categoryBankMap={categoryBankMap}
                            categoryId={selectedCategoryId}
                            required
                            value={selectedBankId || availableBanks[0]?.id || ''}
                            onChange={setSelectedBankId}
                        />
                        <InputError message={errors.bank_id} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="amount">Amount</Label>
                        <Input
                            id="amount"
                            name="amount"
                            type="number"
                            step="0.01"
                            min="0.01"
                            value={amount}
                            onChange={(event) => setAmount(event.target.value)}
                            required
                        />
                        {selectedBalance?.remaining !== null && amount !== '' && (
                            <p className="text-xs text-muted-foreground">
                                After this transfer: {formatMoney(projectedRemaining)} remaining in{' '}
                                {selectedBalance?.name}
                            </p>
                        )}
                        <InputError message={errors.amount} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="description">What was this for?</Label>
                        <Input
                            id="description"
                            name="description"
                            placeholder="Payroll allocation, emergency fund deposit…"
                            required
                        />
                        <InputError message={errors.description} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="transferred_on">Date</Label>
                        <Input
                            id="transferred_on"
                            name="transferred_on"
                            type="date"
                            defaultValue={todayString()}
                            required
                        />
                        <InputError message={errors.transferred_on} />
                    </div>

                    <Button type="submit">Record transfer</Button>
                </Form>
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
                                        {transfer.categoryName} · {transfer.transferredOn}
                                        {transfer.bankName ? ` · ${transfer.bankName}` : ''}
                                    </p>
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
