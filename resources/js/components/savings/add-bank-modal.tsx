import { Form, usePage } from '@inertiajs/react';
import { useState } from 'react';
import BankInstitutionPicker, {
    type BankInstitutionSelection,
} from '@/components/savings/bank-institution-picker';
import GoSaveSpaceSetup, {
    createDefaultGoSaveSpaces,
} from '@/components/savings/gosave-space-setup';
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

export default function AddBankModal({
    open,
    onOpenChange,
    institutions,
}: Props) {
    const { currentTeam } = usePage<SharedData>().props;
    const teamSlug = currentTeam?.slug ?? '';
    const [formKey, setFormKey] = useState(0);
    const [selection, setSelection] = useState<BankInstitutionSelection>(null);

    const selectedInstitution =
        selection?.mode === 'institution'
            ? institutions.find(
                  (institution) => institution.id === selection.institutionId,
              )
            : undefined;
    const savingsSpacesConfig = selectedInstitution?.savingsSpaces ?? null;

    const [mainLabel, setMainLabel] = useState('');
    const [spaces, setSpaces] = useState<
        Array<{ enabled: boolean; label: string }>
    >([]);

    function resetFormState() {
        setSelection(null);
        setMainLabel('');
        setSpaces([]);
        setFormKey((key) => key + 1);
    }

    function handleOpenChange(nextOpen: boolean) {
        if (!nextOpen) {
            resetFormState();
        }

        onOpenChange(nextOpen);
    }

    function handleSelectionChange(nextSelection: BankInstitutionSelection) {
        setSelection(nextSelection);

        const institution =
            nextSelection?.mode === 'institution'
                ? institutions.find(
                      (item) => item.id === nextSelection.institutionId,
                  )
                : undefined;

        if (institution?.savingsSpaces) {
            setMainLabel(institution.savingsSpaces.mainLabel);
            setSpaces(createDefaultGoSaveSpaces(institution.savingsSpaces));

            return;
        }

        setMainLabel('');
        setSpaces([]);
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
                                onChange={handleSelectionChange}
                            />

                            {savingsSpacesConfig ? (
                                <GoSaveSpaceSetup
                                    config={savingsSpacesConfig}
                                    mainLabel={mainLabel}
                                    onMainLabelChange={setMainLabel}
                                    spaces={spaces}
                                    onSpacesChange={setSpaces}
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
                                    disabled={
                                        processing ||
                                        !canSubmitSelection(selection)
                                    }
                                >
                                    {savingsSpacesConfig
                                        ? 'Add GoTyme account'
                                        : 'Add bank'}
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
