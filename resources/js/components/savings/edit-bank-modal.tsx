import { Form, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { BankLogo } from '@/components/savings/bank-select';
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
import type { SharedData } from '@/types';
import type { Bank } from '@/types/savings';

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    bank: Bank | null;
};

export default function EditBankModal({ open, onOpenChange, bank }: Props) {
    const { currentTeam } = usePage<SharedData>().props;
    const teamSlug = currentTeam?.slug ?? '';
    const [formKey, setFormKey] = useState(0);

    function handleOpenChange(nextOpen: boolean) {
        if (nextOpen) {
            setFormKey((key) => key + 1);
        }

        onOpenChange(nextOpen);
    }

    if (bank === null) {
        return null;
    }

    const isCustomBank = !bank.institutionId;

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogContent className="sm:max-w-lg">
                <Form
                    key={`${bank.id}-${formKey}`}
                    action={`/${teamSlug}/savings/banks/${bank.id}`}
                    method="put"
                    className="space-y-6"
                    onSuccess={() => onOpenChange(false)}
                >
                    {({ processing }) => (
                        <>
                            <DialogHeader>
                                <DialogTitle>Edit bank</DialogTitle>
                                <DialogDescription>
                                    Update how this account appears in your
                                    savings plan and reports.
                                </DialogDescription>
                            </DialogHeader>

                            {!isCustomBank && (
                                <div className="flex items-center gap-3 rounded-md border p-3 text-sm">
                                    <BankLogo
                                        logoUrl={bank.logoUrl}
                                        name={bank.name}
                                    />
                                    <div>
                                        <p className="font-medium">
                                            {bank.name}
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            Linked institution
                                        </p>
                                    </div>
                                </div>
                            )}

                            {isCustomBank && (
                                <div className="grid gap-2">
                                    <Label htmlFor="edit-bank-name">
                                        Bank name
                                    </Label>
                                    <Input
                                        id="edit-bank-name"
                                        name="name"
                                        defaultValue={bank.name}
                                        required
                                    />
                                </div>
                            )}

                            {!isCustomBank && (
                                <input
                                    type="hidden"
                                    name="name"
                                    value={bank.name}
                                />
                            )}

                            <div className="grid gap-2">
                                <Label htmlFor="edit-account_label">
                                    Account label (optional)
                                </Label>
                                <Input
                                    id="edit-account_label"
                                    name="account_label"
                                    defaultValue={bank.accountLabel ?? ''}
                                    placeholder="Savings, Payroll, Main…"
                                />
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
