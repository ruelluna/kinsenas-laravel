import { Form, Head, usePage } from '@inertiajs/react';
import { ChevronDown } from 'lucide-react';
import { useMemo, useState } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatMoney } from '@/lib/format-money';
import type { FundBalance, FundSpend } from '@/types/savings';
import type { SharedData } from '@/types';

type Props = {
    plan: { id: string; name: string; hasLockedIncome: boolean };
    fundBalances: FundBalance[];
    defaultCategoryId: string | null;
    banks: Array<{ id: string; name: string }>;
    recipients: Array<{ id: string; name: string }>;
    categories: Array<{ id: string; name: string }>;
    spends: FundSpend[];
};

function todayString(): string {
    return new Date().toISOString().slice(0, 10);
}

function remainingAfterSpend(remaining: string | null, amount: string): string | null {
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

export default function SpendingIndex({
    plan,
    fundBalances,
    defaultCategoryId,
    banks,
    recipients,
    categories,
    spends,
}: Props) {
    const { currentTeam, errors } = usePage<SharedData & { errors: Record<string, string> }>().props;
    const teamSlug = currentTeam?.slug ?? '';
    const [selectedCategoryId, setSelectedCategoryId] = useState(defaultCategoryId ?? categories[0]?.id ?? '');
    const [amount, setAmount] = useState('');
    const [bankDetailsOpen, setBankDetailsOpen] = useState(false);

    const selectedBalance = useMemo(
        () => fundBalances.find((balance) => balance.categoryId === selectedCategoryId) ?? null,
        [fundBalances, selectedCategoryId],
    );

    const projectedRemaining = remainingAfterSpend(selectedBalance?.remaining ?? null, amount);

    return (
        <>
            <Head title="Spending" />
            <Heading
                variant="small"
                title="Spending"
                description={`Track spending from ${plan.name} fund balances.`}
            />

            {!plan.hasLockedIncome && (
                <p className="mt-4 rounded-lg border border-dashed p-4 text-sm text-muted-foreground">
                    Lock at least one income period before recording spending. Fund balances come from
                    locked income allocations.
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
                                Spend from {balance.name}
                            </Button>
                        </div>
                    ))}
                </div>
            )}

            {plan.hasLockedIncome && (
                <Form
                    action={`/${teamSlug}/savings/spending`}
                    method="post"
                    className="mt-8 max-w-lg space-y-4 rounded-lg border p-4"
                >
                    <div>
                        <h3 className="font-medium">Record spending</h3>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Quick entry for daily expenses. Bank details are optional.
                        </p>
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="category_id">Fund</Label>
                        <select
                            id="category_id"
                            name="category_id"
                            className="border-input h-9 rounded-md border px-3 text-sm"
                            value={selectedCategoryId}
                            onChange={(event) => setSelectedCategoryId(event.target.value)}
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
                                After this spend: {formatMoney(projectedRemaining)} remaining in{' '}
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
                            placeholder="Groceries, car repair, tithe…"
                            required
                        />
                        <InputError message={errors.description} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="spent_on">Date</Label>
                        <Input id="spent_on" name="spent_on" type="date" defaultValue={todayString()} required />
                        <InputError message={errors.spent_on} />
                    </div>

                    <Collapsible open={bankDetailsOpen} onOpenChange={setBankDetailsOpen}>
                        <CollapsibleTrigger asChild>
                            <Button type="button" variant="ghost" size="sm" className="gap-2 px-0">
                                <ChevronDown
                                    className={`size-4 transition-transform ${bankDetailsOpen ? 'rotate-180' : ''}`}
                                />
                                Bank details (optional)
                            </Button>
                        </CollapsibleTrigger>
                        <CollapsibleContent className="mt-3 space-y-4">
                            <div className="grid gap-2">
                                <Label htmlFor="bank_id">Bank</Label>
                                <select
                                    id="bank_id"
                                    name="bank_id"
                                    className="border-input h-9 rounded-md border px-3 text-sm"
                                    defaultValue=""
                                >
                                    <option value="">None</option>
                                    {banks.map((bank) => (
                                        <option key={bank.id} value={bank.id}>
                                            {bank.name}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.bank_id} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="recipient_id">Recipient</Label>
                                <select
                                    id="recipient_id"
                                    name="recipient_id"
                                    className="border-input h-9 rounded-md border px-3 text-sm"
                                    defaultValue=""
                                >
                                    <option value="">None</option>
                                    {recipients.map((recipient) => (
                                        <option key={recipient.id} value={recipient.id}>
                                            {recipient.name}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.recipient_id} />
                            </div>
                            <p className="text-xs text-muted-foreground">
                                When a bank is selected, spending stays pending until you confirm it.
                            </p>
                        </CollapsibleContent>
                    </Collapsible>

                    <Button type="submit">Record spending</Button>
                </Form>
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
                                </div>
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
