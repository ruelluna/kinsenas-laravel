import { Form } from '@inertiajs/react';
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
import { formatBankOptionLabel } from '@/lib/format-bank-label';
import type { Bank } from '@/types/savings';

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    bank: Bank | null;
    teamSlug: string;
};

export default function DeleteBankModal({
    open,
    onOpenChange,
    bank,
    teamSlug,
}: Props) {
    if (bank === null) {
        return null;
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <Form
                    action={`/${teamSlug}/savings/banks/${bank.id}`}
                    method="delete"
                    className="space-y-6"
                    onSuccess={() => onOpenChange(false)}
                >
                    {({ processing }) => (
                        <>
                            <DialogHeader>
                                <DialogTitle>Remove bank?</DialogTitle>
                                <DialogDescription>
                                    This removes{' '}
                                    <strong>
                                        {formatBankOptionLabel(bank)}
                                    </strong>{' '}
                                    from your team. Fund buckets assigned to
                                    this account will be unlinked, but your
                                    spending and transfer history stays intact.
                                </DialogDescription>
                            </DialogHeader>

                            <DialogFooter className="gap-2">
                                <DialogClose asChild>
                                    <Button type="button" variant="secondary">
                                        Cancel
                                    </Button>
                                </DialogClose>
                                <Button
                                    type="submit"
                                    variant="destructive"
                                    disabled={processing}
                                >
                                    Remove bank
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
