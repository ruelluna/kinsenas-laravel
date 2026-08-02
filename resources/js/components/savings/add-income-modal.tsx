import { Form, usePage } from '@inertiajs/react';
import { useState } from 'react';
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

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export default function AddIncomeModal({ open, onOpenChange }: Props) {
    const { currentTeam } = usePage<SharedData>().props;
    const teamSlug = currentTeam?.slug ?? '';
    const [formKey, setFormKey] = useState(0);

    function handleOpenChange(nextOpen: boolean) {
        if (nextOpen) {
            setFormKey((key) => key + 1);
        }

        onOpenChange(nextOpen);
    }

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogContent>
                <Form
                    key={formKey}
                    action={`/${teamSlug}/savings/income`}
                    method="post"
                    className="space-y-6"
                    onSuccess={() => onOpenChange(false)}
                >
                    {({ processing }) => (
                        <>
                            <DialogHeader>
                                <DialogTitle>Add income</DialogTitle>
                                <DialogDescription>
                                    Enter a monthly income period. Lock it when
                                    ready to enable transfers and spending.
                                </DialogDescription>
                            </DialogHeader>

                            <div className="grid gap-2">
                                <Label htmlFor="name">Income name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    type="text"
                                    placeholder="e.g. January salary"
                                    required
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="period_start">
                                    Period start
                                </Label>
                                <Input
                                    id="period_start"
                                    name="period_start"
                                    type="date"
                                    required
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="amount">
                                    Income amount (PHP)
                                </Label>
                                <Input
                                    id="amount"
                                    name="amount"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    required
                                />
                            </div>

                            <DialogFooter className="gap-2">
                                <DialogClose asChild>
                                    <Button type="button" variant="secondary">
                                        Cancel
                                    </Button>
                                </DialogClose>
                                <Button type="submit" disabled={processing}>
                                    Save income
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
