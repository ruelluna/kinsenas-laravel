import { Form, usePage } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import InputError from '@/components/input-error';
import ReceiptUploadField from '@/components/savings/receipt-upload-field';
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
import type { FundBalance } from '@/types/savings';

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    presetCategoryId?: string | null;
    defaultCategoryId: string | null;
    categories: Array<{ id: string; name: string; bankId: string | null }>;
    fundBalances: FundBalance[];
    recipients: Array<{ id: string; name: string }>;
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

export default function AddSpendingModal({
    open,
    onOpenChange,
    presetCategoryId,
    defaultCategoryId,
    categories,
    fundBalances,
    recipients,
}: Props) {
    const { currentTeam, errors } = usePage<SharedData & { errors: Record<string, string> }>().props;
    const teamSlug = currentTeam?.slug ?? '';
    const [formKey, setFormKey] = useState(0);
    const [selectedCategoryId, setSelectedCategoryId] = useState('');
    const [amount, setAmount] = useState('');

    useEffect(() => {
        if (open) {
            setSelectedCategoryId(presetCategoryId ?? defaultCategoryId ?? categories[0]?.id ?? '');
            setAmount('');
            setFormKey((key) => key + 1);
        }
    }, [open, presetCategoryId, defaultCategoryId, categories]);

    const selectedBalance = useMemo(
        () => fundBalances.find((balance) => balance.categoryId === selectedCategoryId) ?? null,
        [fundBalances, selectedCategoryId],
    );

    const projectedRemaining = remainingAfterSpend(selectedBalance?.remaining ?? null, amount);

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
                <Form
                    key={formKey}
                    action={`/${teamSlug}/savings/spending`}
                    method="post"
                    encType="multipart/form-data"
                    className="space-y-4"
                    onSuccess={() => onOpenChange(false)}
                >
                    {({ processing }) => (
                        <>
                            <DialogHeader>
                                <DialogTitle>Record spending</DialogTitle>
                                <DialogDescription>
                                    Quick entry for daily expenses.
                                </DialogDescription>
                            </DialogHeader>

                            <div className="grid gap-2">
                                <Label htmlFor="category_id">Fund bucket</Label>
                                <select
                                    id="category_id"
                                    name="category_id"
                                    className="border-input h-9 rounded-md border px-3 text-sm"
                                    value={selectedCategoryId}
                                    onChange={(event) =>
                                        setSelectedCategoryId(event.target.value)
                                    }
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
                                        After this spend: {formatMoney(projectedRemaining)}{' '}
                                        remaining in {selectedBalance?.name}
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
                                <Input
                                    id="spent_on"
                                    name="spent_on"
                                    type="date"
                                    defaultValue={todayString()}
                                    required
                                />
                                <InputError message={errors.spent_on} />
                            </div>

                            <ReceiptUploadField
                                resetKey={formKey}
                                error={errors.receipt_image}
                            />

                            <div className="grid gap-2">
                                <Label htmlFor="recipient_id">Recipient (optional)</Label>
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

                            <DialogFooter className="gap-2">
                                <DialogClose asChild>
                                    <Button type="button" variant="secondary">
                                        Cancel
                                    </Button>
                                </DialogClose>
                                <Button type="submit" disabled={processing}>
                                    Record spending
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
