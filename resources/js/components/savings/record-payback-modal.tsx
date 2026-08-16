import { Form, usePage } from '@inertiajs/react';
import { useState } from 'react';
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
import type { FundSpend } from '@/types/savings';

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    spend: FundSpend | null;
};

function todayString(): string {
    return new Date().toISOString().slice(0, 10);
}

export default function RecordPaybackModal({
    open,
    onOpenChange,
    spend,
}: Props) {
    const { currentTeam, errors } = usePage<
        SharedData & { errors: Record<string, string> }
    >().props;
    const teamSlug = currentTeam?.slug ?? '';
    const [amount, setAmount] = useState('');

    if (!spend) {
        return null;
    }

    const defaultAmount = spend.remainingOwed ?? spend.amount ?? '';

    return (
        <Dialog
            open={open}
            onOpenChange={(nextOpen) => {
                if (nextOpen) {
                    setAmount(defaultAmount);
                }
                onOpenChange(nextOpen);
            }}
        >
            <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
                {open ? (
                    <Form
                        key={spend.id}
                        action={`/${teamSlug}/savings/spending/${spend.id}/reimbursements`}
                        method="post"
                        className="space-y-4"
                        onSuccess={() => onOpenChange(false)}
                    >
                        {({ processing }) => (
                            <>
                                <DialogHeader>
                                    <DialogTitle>Record payback</DialogTitle>
                                    <DialogDescription>
                                        {spend.description} ·{' '}
                                        {spend.categoryName}
                                        {spend.expectedFromRecipientName
                                            ? ` · from ${spend.expectedFromRecipientName}`
                                            : ''}
                                    </DialogDescription>
                                </DialogHeader>

                                <p className="text-sm text-muted-foreground">
                                    Remaining owed:{' '}
                                    {formatMoney(spend.remainingOwed)}
                                </p>

                                <div className="grid gap-2">
                                    <Label htmlFor="payback_amount">
                                        Amount received
                                    </Label>
                                    <Input
                                        id="payback_amount"
                                        name="amount"
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        data-test="payback-amount"
                                        defaultValue={defaultAmount}
                                        value={amount}
                                        onChange={(event) =>
                                            setAmount(event.target.value)
                                        }
                                        required
                                    />
                                    <InputError message={errors.amount} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="received_on">Date</Label>
                                    <Input
                                        id="received_on"
                                        name="received_on"
                                        type="date"
                                        defaultValue={todayString()}
                                        required
                                    />
                                    <InputError message={errors.received_on} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="notes">Note (optional)</Label>
                                    <Input
                                        id="notes"
                                        name="notes"
                                        placeholder="GCash, cash, etc."
                                    />
                                    <InputError message={errors.notes} />
                                </div>

                                <DialogFooter className="gap-2">
                                    <DialogClose asChild>
                                        <Button type="button" variant="secondary">
                                            Cancel
                                        </Button>
                                    </DialogClose>
                                    <Button type="submit" disabled={processing} data-test="record-payback-submit">
                                        Record payback
                                    </Button>
                                </DialogFooter>
                            </>
                        )}
                    </Form>
                ) : null}
            </DialogContent>
        </Dialog>
    );
}
