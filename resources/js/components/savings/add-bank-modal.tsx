import { Form, usePage } from '@inertiajs/react';
import { useState } from 'react';
import BankInstitutionPicker, {
    type BankInstitutionSelection,
} from '@/components/savings/bank-institution-picker';
import GoTymeAccountLabel from '@/components/savings/gotyme-account-label';
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
import type { BankInstitution } from '@/types/savings';

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    institutions: BankInstitution[];
};

function canSubmitSelection(selection: BankInstitutionSelection): boolean {
    if (selection === null) {
        return false;
    }

    if (selection.mode === 'institution') {
        return selection.institutionId !== '';
    }

    return selection.name.trim() !== '';
}

function isGoTymeInstitution(
    institution: BankInstitution | undefined,
): boolean {
    return institution?.slug === 'gotyme';
}

export default function AddBankModal({
    open,
    onOpenChange,
    institutions,
}: Props) {
    const { currentTeam } = usePage<SharedData>().props;
    const teamSlug = currentTeam?.slug ?? '';
    const [formKey, setFormKey] = useState(0);
    const [selection, setSelection] = useState<BankInstitutionSelection>(null);
    const [, setGoTymeAccountLabel] = useState('GoTyme/Main');

    const selectedInstitution =
        selection?.mode === 'institution'
            ? institutions.find(
                  (institution) => institution.id === selection.institutionId,
              )
            : undefined;
    const isGoTyme = isGoTymeInstitution(selectedInstitution);

    function resetFormState() {
        setSelection(null);
        setGoTymeAccountLabel('GoTyme/Main');
        setFormKey((key) => key + 1);
    }

    function handleOpenChange(nextOpen: boolean) {
        if (!nextOpen) {
            resetFormState();
        }

        onOpenChange(nextOpen);
    }

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
                <Form
                    key={formKey}
                    action={`/${teamSlug}/savings/banks`}
                    method="post"
                    className="space-y-6"
                    onSuccess={() => handleOpenChange(false)}
                >
                    {({ processing }) => (
                        <>
                            <DialogHeader>
                                <DialogTitle>Add bank</DialogTitle>
                                <DialogDescription>
                                    Link a bank account where you hold savings.
                                    Assign fund buckets to each account in your
                                    savings plan.
                                </DialogDescription>
                            </DialogHeader>

                            <BankInstitutionPicker
                                institutions={institutions}
                                onChange={setSelection}
                            />

                            {isGoTyme ? (
                                <GoTymeAccountLabel
                                    onLabelChange={setGoTymeAccountLabel}
                                />
                            ) : (
                                selection !== null && (
                                    <div className="grid gap-2">
                                        <Label htmlFor="account_label">
                                            Account label (optional)
                                        </Label>
                                        <Input
                                            id="account_label"
                                            name="account_label"
                                            placeholder="Savings, Payroll, Main…"
                                        />
                                    </div>
                                )
                            )}

                            <DialogFooter className="gap-2">
                                <DialogClose asChild>
                                    <Button type="button" variant="secondary">
                                        Cancel
                                    </Button>
                                </DialogClose>
                                <Button
                                    type="submit"
                                    data-test="add-bank-submit"
                                    disabled={
                                        processing ||
                                        !canSubmitSelection(selection)
                                    }
                                >
                                    Add bank
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
