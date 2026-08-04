import { Form } from '@inertiajs/react';
import { AlertCircle } from 'lucide-react';
import InputError from '@/components/input-error';
import { Alert, AlertDescription } from '@/components/ui/alert';
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

type Props = {
    periodId: string;
    periodName: string;
    teamSlug: string;
    open: boolean;
    onOpenChange: (open: boolean) => void;
    blockReason?: string | null;
};

export default function DeleteIncomeModal({
    periodId,
    periodName,
    teamSlug,
    open,
    onOpenChange,
    blockReason = null,
}: Props) {
    const isBlocked = Boolean(blockReason);

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <Form
                    action={`/${teamSlug}/savings/income/${periodId}`}
                    method="delete"
                    className="space-y-6"
                    onSuccess={() => onOpenChange(false)}
                >
                    {({ processing, errors }) => (
                        <>
                            <DialogHeader>
                                <DialogTitle>Delete income period?</DialogTitle>
                                <DialogDescription>
                                    This removes <strong>{periodName}</strong>{' '}
                                    and its fund allocations. You cannot delete
                                    income if transfers or spending already
                                    exceed what would remain in a fund bucket.
                                </DialogDescription>
                            </DialogHeader>

                            {isBlocked && (
                                <Alert variant="destructive">
                                    <AlertCircle />
                                    <AlertDescription>
                                        {blockReason}
                                    </AlertDescription>
                                </Alert>
                            )}

                            <InputError message={errors.period} />

                            <DialogFooter className="gap-2">
                                <DialogClose asChild>
                                    <Button type="button" variant="secondary">
                                        Cancel
                                    </Button>
                                </DialogClose>
                                <Button
                                    type="submit"
                                    variant="destructive"
                                    disabled={processing || isBlocked}
                                >
                                    Delete income
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
