import { Form, usePage } from '@inertiajs/react';
import { useMemo, useRef, useState } from 'react';
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
import type { FundBalance, FundSpend } from '@/types/savings';

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    spend: FundSpend | null;
    categories: Array<{ id: string; name: string; bankId: string | null }>;
    fundBalances: FundBalance[];
    recipients: Array<{ id: string; name: string }>;
};

type FormProps = Omit<Props, 'open' | 'onOpenChange'> & {
    spend: FundSpend;
    onClose: () => void;
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

function EditSpendingForm({
    onClose,
    spend,
    categories,
    fundBalances,
    recipients,
}: FormProps) {
    const { currentTeam, errors } = usePage<SharedData & { errors: Record<string, string> }>().props;
    const teamSlug = currentTeam?.slug ?? '';
    const [selectedCategoryId, setSelectedCategoryId] = useState(spend.categoryId);
    const [amount, setAmount] = useState(spend.amount ?? '');
    const [description, setDescription] = useState(spend.description ?? '');
    const [spentOn, setSpentOn] = useState(spend.spentOn);
    const [recipientId, setRecipientId] = useState(spend.recipientId ?? '');
    const [removeReceipt, setRemoveReceipt] = useState(false);

    const selectedBalance = useMemo(
        () => fundBalances.find((balance) => balance.categoryId === selectedCategoryId) ?? null,
        [fundBalances, selectedCategoryId],
    );

    const projectedRemaining = remainingAfterSpend(
        selectedBalance?.remaining ?? null,
        amount,
        spend.categoryId === selectedCategoryId ? spend.amount : null,
    );

    return (
        <Form
            action={`/${teamSlug}/savings/spending/${spend.id}`}
            method="put"
            encType="multipart/form-data"
            className="space-y-4"
            onSuccess={onClose}
        >
            {({ processing }) => (
                <>
                    <DialogHeader>
                        <DialogTitle>Edit spending</DialogTitle>
                        <DialogDescription>
                            Update amount, fund bucket, date, or description.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="grid gap-2">
                        <Label htmlFor="edit_category_id">Fund bucket</Label>
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
                                After this spend: {formatMoney(projectedRemaining)} remaining in{' '}
                                {selectedBalance?.name}
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

                    {removeReceipt && <input type="hidden" name="remove_receipt" value="1" />}

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
    );
}

export default function EditSpendingModal({
    open,
    onOpenChange,
    spend,
    categories,
    fundBalances,
    recipients,
}: Props) {
    if (!spend) {
        return null;
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
                {open ? (
                    <EditSpendingForm
                        key={spend.id}
                        spend={spend}
                        categories={categories}
                        fundBalances={fundBalances}
                        recipients={recipients}
                        onClose={() => onOpenChange(false)}
                    />
                ) : null}
            </DialogContent>
        </Dialog>
    );
}
