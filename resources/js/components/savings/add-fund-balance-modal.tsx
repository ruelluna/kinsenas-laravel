import { Form, usePage } from '@inertiajs/react';
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

export type ExistingFundTarget = {
    categoryId: string;
    name: string;
    openingBalance?: string | null;
};

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    target: ExistingFundTarget | null;
};

export default function AddFundBalanceModal({
    open,
    onOpenChange,
    target,
}: Props) {
    const { currentTeam, errors } = usePage<
        SharedData & { errors: Record<string, string> }
    >().props;
    const teamSlug = currentTeam?.slug ?? '';

    if (!target) {
        return null;
    }

    const currentTotal = target.openingBalance ?? '0.00';

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        Add existing fund to {target.name}
                    </DialogTitle>
                    <DialogDescription>
                        Record money you already have in this fund bucket.
                        Locked income adds payday allocations on top.
                    </DialogDescription>
                </DialogHeader>

                <Form
                    key={target.categoryId}
                    action={`/${teamSlug}/savings/plan/categories/${target.categoryId}/opening-balance`}
                    method="patch"
                    resetOnSuccess
                    onSuccess={() => onOpenChange(false)}
                    className="space-y-4"
                >
                    {({ processing }) => (
                        <>
                            <div className="rounded-lg border bg-muted/40 px-3 py-2 text-sm">
                                <p className="text-muted-foreground">
                                    Existing fund in this bucket
                                </p>
                                <p className="font-medium">
                                    {formatMoney(currentTotal)}
                                </p>
                            </div>

                            <div>
                                <Label htmlFor="fund-amount">
                                    Amount to add (PHP)
                                </Label>
                                <Input
                                    id="fund-amount"
                                    name="amount"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    required
                                    autoFocus
                                    placeholder="0.00"
                                />
                                <InputError message={errors.amount} />
                            </div>

                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button type="button" variant="outline">
                                        Cancel
                                    </Button>
                                </DialogClose>
                                <Button type="submit" disabled={processing}>
                                    Add Existing Fund
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
