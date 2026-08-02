import { Form, usePage } from '@inertiajs/react';
import { useState } from 'react';
import BankInstitutionPicker from '@/components/savings/bank-institution-picker';
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

export default function AddBankModal({ open, onOpenChange, institutions }: Props) {
    const { currentTeam } = usePage<SharedData>().props;
    const teamSlug = currentTeam?.slug ?? '';
    const [formKey, setFormKey] = useState(0);
    const [institutionSelection, setInstitutionSelection] = useState<{
        institutionId: string;
        name: string;
    } | null>(null);

    const selectedInstitution = institutions.find(
        (institution) => institution.id === institutionSelection?.institutionId,
    );
    const savingsSpacesConfig = selectedInstitution?.savingsSpaces ?? null;

    const [mainLabel, setMainLabel] = useState('');
    const [spaces, setSpaces] = useState<Array<{ enabled: boolean; label: string }>>([]);

    function resetFormState() {
        setInstitutionSelection(null);
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

    function handleInstitutionChange(
        selection: { institutionId: string; name: string } | null,
    ) {
        setInstitutionSelection(selection);

        const institution = institutions.find((item) => item.id === selection?.institutionId);

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
                                    Link a bank account where you hold savings. Assign fund buckets to
                                    each account in your savings plan.
                                </DialogDescription>
                            </DialogHeader>

                            <BankInstitutionPicker
                                institutions={institutions}
                                onChange={handleInstitutionChange}
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
                                <div className="grid gap-2">
                                    <Label htmlFor="account_label">Account label (optional)</Label>
                                    <Input
                                        id="account_label"
                                        name="account_label"
                                        placeholder="Savings, Payroll, Main…"
                                    />
                                </div>
                            )}

                            <DialogFooter className="gap-2">
                                <DialogClose asChild>
                                    <Button type="button" variant="secondary">
                                        Cancel
                                    </Button>
                                </DialogClose>
                                <Button
                                    type="submit"
                                    disabled={processing || institutionSelection === null}
                                >
                                    {savingsSpacesConfig ? 'Add GoTyme account' : 'Add bank'}
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
