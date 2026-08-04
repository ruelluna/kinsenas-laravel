import { Form, Link } from '@inertiajs/react';
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
import { formatMoney } from '@/lib/format-money';
import type { IncomeDistributionTodo } from '@/types/savings';

type Props = {
    todo: IncomeDistributionTodo;
    periodId: string;
    teamSlug: string;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export default function ConfirmIncomeDistributionTodoModal({
    todo,
    periodId,
    teamSlug,
    open,
    onOpenChange,
}: Props) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
                <Form
                    action={`/${teamSlug}/savings/income/${periodId}/todos/${todo.id}/complete`}
                    method="post"
                    onSuccess={() => onOpenChange(false)}
                >
                    {({ processing }) => (
                        <>
                            <DialogHeader>
                                <DialogTitle>Confirm bank transfer?</DialogTitle>
                                <DialogDescription asChild>
                                    <div className="space-y-3 text-sm text-muted-foreground">
                                        <p>
                                            Kinsenas cannot see your bank
                                            activity. Mark this complete only
                                            after you have moved{' '}
                                            <strong>
                                                {formatMoney(todo.amount)}
                                            </strong>{' '}
                                            to{' '}
                                            {todo.bankDisplayName ? (
                                                <>
                                                    <strong>
                                                        {todo.bankDisplayName}
                                                    </strong>{' '}
                                                    ({todo.categoryName})
                                                </>
                                            ) : (
                                                <>
                                                    your assigned bank for{' '}
                                                    <strong>
                                                        {todo.categoryName}
                                                    </strong>
                                                </>
                                            )}{' '}
                                            in your banking app.
                                        </p>
                                        <p>
                                            If you mark complete without
                                            transferring, your fund balances and
                                            reports will not reflect what you
                                            actually have.
                                        </p>
                                        {!todo.bankDisplayName && (
                                            <p>
                                                <Link
                                                    href={`/${teamSlug}/savings/plan`}
                                                    className="text-primary underline-offset-4 hover:underline"
                                                >
                                                    Assign a bank on your plan
                                                </Link>{' '}
                                                so this checklist shows where to
                                                move the money.
                                            </p>
                                        )}
                                    </div>
                                </DialogDescription>
                            </DialogHeader>

                            <DialogFooter className="gap-2">
                                <DialogClose asChild>
                                    <Button type="button" variant="secondary">
                                        Cancel
                                    </Button>
                                </DialogClose>
                                <Button type="submit" disabled={processing}>
                                    I transferred this — mark complete
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
