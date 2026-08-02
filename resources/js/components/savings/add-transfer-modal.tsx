import { Form, usePage } from '@inertiajs/react';
import { useEffect, useMemo, useRef, useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatMoney } from '@/lib/format-money';
import type { SharedData } from '@/types';
import type { CategoryBankMap, FundBalance } from '@/types/savings';

type CategoryOption = {
    id: string;
    name: string;
    bankId: string | null;
    bankName: string | null;
    bankLogoUrl: string | null;
};

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    presetFromCategoryId?: string | null;
    defaultCategoryId: string | null;
    categories: CategoryOption[];
    categoryBankMap: CategoryBankMap;
    fundBalances: FundBalance[];
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

function bankLabel(category: CategoryOption | undefined): string {
    if (!category?.bankName) {
        return 'No bank assigned';
    }

    return category.bankName;
}

export default function AddTransferModal({
    open,
    onOpenChange,
    presetFromCategoryId,
    defaultCategoryId,
    categories,
    categoryBankMap,
    fundBalances,
}: Props) {
    const { currentTeam, errors, name } = usePage<SharedData & { errors: Record<string, string> }>().props;
    const teamSlug = currentTeam?.slug ?? '';
    const submitRef = useRef<HTMLButtonElement>(null);
    const skipBankReminderRef = useRef(false);
    const [formKey, setFormKey] = useState(0);
    const [fromCategoryId, setFromCategoryId] = useState('');
    const [toCategoryId, setToCategoryId] = useState('');
    const [amount, setAmount] = useState('');
    const [bankReminderOpen, setBankReminderOpen] = useState(false);

    useEffect(() => {
        if (open) {
            setFromCategoryId(presetFromCategoryId ?? defaultCategoryId ?? categories[0]?.id ?? '');
            setToCategoryId('');
            setAmount('');
            setFormKey((key) => key + 1);
        }
    }, [open, presetFromCategoryId, defaultCategoryId, categories]);

    const fromCategory = useMemo(
        () => categories.find((category) => category.id === fromCategoryId),
        [categories, fromCategoryId],
    );

    const toCategory = useMemo(
        () => categories.find((category) => category.id === toCategoryId),
        [categories, toCategoryId],
    );

    const fromBalance = useMemo(
        () => fundBalances.find((balance) => balance.categoryId === fromCategoryId) ?? null,
        [fundBalances, fromCategoryId],
    );

    const toCategoryOptions = useMemo(
        () => categories.filter((category) => category.id !== fromCategoryId),
        [categories, fromCategoryId],
    );

    const crossesBanks = useMemo(() => {
        const fromBankId = categoryBankMap[fromCategoryId] ?? null;
        const toBankId = toCategoryId ? (categoryBankMap[toCategoryId] ?? null) : null;

        return fromBankId !== toBankId;
    }, [categoryBankMap, fromCategoryId, toCategoryId]);

    const projectedFromRemaining = remainingAfterAmount(fromBalance?.remaining ?? null, amount);

    function confirmCrossBankTransfer() {
        setBankReminderOpen(false);
        skipBankReminderRef.current = true;
        submitRef.current?.click();
    }

    function handleOpenChange(nextOpen: boolean) {
        if (!nextOpen) {
            setBankReminderOpen(false);
        }

        onOpenChange(nextOpen);
    }

    return (
        <>
            <Dialog open={open} onOpenChange={handleOpenChange}>
                <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
                    <Form
                        key={formKey}
                        action={`/${teamSlug}/savings/transfers`}
                        method="post"
                        className="space-y-4"
                        onSuccess={() => handleOpenChange(false)}
                        onSubmit={(event) => {
                            if (skipBankReminderRef.current) {
                                skipBankReminderRef.current = false;

                                return;
                            }

                            if (crossesBanks && toCategoryId !== '') {
                                event.preventDefault();
                                setBankReminderOpen(true);
                            }
                        }}
                    >
                        {({ processing }) => (
                            <>
                                <DialogHeader>
                                    <DialogTitle>Record transfer</DialogTitle>
                                    <DialogDescription>
                                        Move money from one fund to another. When funds use
                                        different banks, move the actual money in your banking app,
                                        then confirm here.
                                    </DialogDescription>
                                </DialogHeader>

                                <div className="grid gap-2">
                                    <Label htmlFor="from_category_id">From fund bucket</Label>
                                    <select
                                        id="from_category_id"
                                        name="from_category_id"
                                        className="border-input h-9 rounded-md border px-3 text-sm"
                                        value={fromCategoryId}
                                        onChange={(event) => {
                                            setFromCategoryId(event.target.value);

                                            if (event.target.value === toCategoryId) {
                                                setToCategoryId('');
                                            }
                                        }}
                                        required
                                    >
                                        {categories.map((category) => (
                                            <option key={category.id} value={category.id}>
                                                {category.name}
                                                {category.bankName ? ` (${category.bankName})` : ''}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.from_category_id} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="to_category_id">To fund bucket</Label>
                                    <select
                                        id="to_category_id"
                                        name="to_category_id"
                                        className="border-input h-9 rounded-md border px-3 text-sm"
                                        value={toCategoryId}
                                        onChange={(event) => setToCategoryId(event.target.value)}
                                        required
                                    >
                                        <option value="">Select a fund</option>
                                        {toCategoryOptions.map((category) => (
                                            <option key={category.id} value={category.id}>
                                                {category.name}
                                                {category.bankName ? ` (${category.bankName})` : ''}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.to_category_id} />
                                </div>

                                {fromCategory && toCategory && (
                                    <p className="text-xs text-muted-foreground">
                                        {crossesBanks ? (
                                            <>
                                                Different banks: move funds from{' '}
                                                <strong>{bankLabel(fromCategory)}</strong> to{' '}
                                                <strong>{bankLabel(toCategory)}</strong> in your
                                                banking app.
                                            </>
                                        ) : (
                                            <>
                                                Same bank ({bankLabel(fromCategory)}): this
                                                transfer will be recorded immediately.
                                            </>
                                        )}
                                    </p>
                                )}

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
                                    {fromBalance?.remaining !== null && amount !== '' && (
                                        <p className="text-xs text-muted-foreground">
                                            After this transfer:{' '}
                                            {formatMoney(projectedFromRemaining)} remaining in{' '}
                                            {fromBalance?.name}
                                        </p>
                                    )}
                                    <InputError message={errors.amount} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="description">What was this for?</Label>
                                    <Input
                                        id="description"
                                        name="description"
                                        placeholder="Rebalance emergency fund, move tithe…"
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

                                <button
                                    ref={submitRef}
                                    type="submit"
                                    className="hidden"
                                    aria-hidden
                                    tabIndex={-1}
                                />

                                <DialogFooter className="gap-2">
                                    <DialogClose asChild>
                                        <Button type="button" variant="secondary">
                                            Cancel
                                        </Button>
                                    </DialogClose>
                                    <Button type="submit" disabled={processing}>
                                        Record transfer
                                    </Button>
                                </DialogFooter>
                            </>
                        )}
                    </Form>
                </DialogContent>
            </Dialog>

            <Dialog open={bankReminderOpen} onOpenChange={setBankReminderOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Move funds between banks</DialogTitle>
                        <DialogDescription>
                            Before confirming in {name}, transfer{' '}
                            {amount ? formatMoney(amount) : 'this amount'} from{' '}
                            <strong>{bankLabel(fromCategory)}</strong> to{' '}
                            <strong>{bankLabel(toCategory)}</strong> in your banking app. This
                            record stays pending until you confirm the money has moved.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => setBankReminderOpen(false)}
                        >
                            Cancel
                        </Button>
                        <Button type="button" onClick={confirmCrossBankTransfer}>
                            I&apos;ll move the funds — record transfer
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}
