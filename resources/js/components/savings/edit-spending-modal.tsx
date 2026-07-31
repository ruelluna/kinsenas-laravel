import { Form, usePage } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import ReceiptUploadField from '@/components/savings/receipt-upload-field';
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
import type { FundBalance, FundSpend } from '@/types/savings';
import type { SharedData } from '@/types';

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    spend: FundSpend | null;
    categories: Array<{ id: string; name: string; bankId: string | null }>;
    fundBalances: FundBalance[];
    recipients: Array<{ id: string; name: string }>;
};

function remainingAfterSpend(remaining: string | null, amount: string, currentAmount: string | null): string | null {
    if (remaining === null || amount === '') {
        return remaining;
    }

    const parsed = parseFloat(amount);

    if (!Number.isFinite(parsed)) {
        return remaining;
    }

    const parsedCurrent = currentAmount !== null ? parseFloat(currentAmount) : 0;
    const adjustedRemaining = parseFloat(remaining) + (Number.isFinite(parsedCurrent) ? parsedCurrent : 0);

    return (adjustedRemaining - parsed).toFixed(2);
}

export default function EditSpendingModal({
    open,
    onOpenChange,
    spend,
    categories,
    fundBalances,
    recipients,
}: Props) {
    const { currentTeam, errors } = usePage<SharedData & { errors: Record<string, string> }>().props;
    const teamSlug = currentTeam?.slug ?? '';
    const [formKey, setFormKey] = useState(0);
    const [selectedCategoryId, setSelectedCategoryId] = useState('');
    const [amount, setAmount] = useState('');
    const [description, setDescription] = useState('');
    const [spentOn, setSpentOn] = useState('');
    const [recipientId, setRecipientId] = useState('');
    const [removeReceipt, setRemoveReceipt] = useState(false);

    useEffect(() => {
        if (open && spend) {
            setSelectedCategoryId(spend.categoryId);
            setAmount(spend.amount ?? '');
            setDescription(spend.description ?? '');
            setSpentOn(spend.spentOn);
            setRecipientId(spend.recipientId ?? '');
            setRemoveReceipt(false);
            setFormKey((key) => key + 1);
        }
    }, [open, spend]);

    const selectedBalance = useMemo(
        () => fundBalances.find((balance) => balance.categoryId === selectedCategoryId) ?? null,
        [fundBalances, selectedCategoryId],
    );

    const projectedRemaining = remainingAfterSpend(
        selectedBalance?.remaining ?? null,
        amount,
        spend?.categoryId === selectedCategoryId ? spend.amount : null,
    );

    if (!spend) {
        return null;
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
                <Form
                    key={formKey}
                    action={`/${teamSlug}/savings/spending/${spend.id}`}
                    method="put"
                    encType="multipart/form-data"
                    className="space-y-4"
                    onSuccess={() => onOpenChange(false)}
                >
                    {({ processing }) => (
                        <>
                            <DialogHeader>
                                <DialogTitle>Edit spending</DialogTitle>
                                <DialogDescription>
                                    Update amount, fund, date, or description.
                                </DialogDescription>
                            </DialogHeader>

                            <div className="grid gap-2">
                                <Label htmlFor="edit_category_id">Fund</Label>
                                <select
                                    id="edit_category_id"
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
                                <Label htmlFor="edit_amount">Amount</Label>
                                <Input
                                    id="edit_amount"
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
                                <Label htmlFor="edit_description">What was this for?</Label>
                                <Input
                                    id="edit_description"
                                    name="description"
                                    value={description}
                                    onChange={(event) => setDescription(event.target.value)}
                                    required
                                />
                                <InputError message={errors.description} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="edit_spent_on">Date</Label>
                                <Input
                                    id="edit_spent_on"
                                    name="spent_on"
                                    type="date"
                                    value={spentOn}
                                    onChange={(event) => setSpentOn(event.target.value)}
                                    required
                                />
                                <InputError message={errors.spent_on} />
                            </div>

                            <ReceiptUploadField
                                resetKey={formKey}
                                error={errors.receipt_image}
                                existingImageUrl={removeReceipt ? null : spend.receiptImageUrl}
                            />

                            {spend.receiptImageUrl && !removeReceipt && (
                                <label className="flex items-center gap-2 text-sm">
                                    <input
                                        type="checkbox"
                                        checked={removeReceipt}
                                        onChange={(event) => setRemoveReceipt(event.target.checked)}
                                    />
                                    Remove receipt image
                                </label>
                            )}

                            {removeReceipt && (
                                <input type="hidden" name="remove_receipt" value="1" />
                            )}

                            <div className="grid gap-2">
                                <Label htmlFor="edit_recipient_id">Recipient (optional)</Label>
                                <select
                                    id="edit_recipient_id"
                                    name="recipient_id"
                                    className="border-input h-9 rounded-md border px-3 text-sm"
                                    value={recipientId}
                                    onChange={(event) => setRecipientId(event.target.value)}
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
                                    Save changes
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
