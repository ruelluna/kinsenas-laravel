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
    recipientTypes: Array<{ value: string; label: string }>;
};

export default function AddRecipientModal({
    open,
    onOpenChange,
    recipientTypes,
}: Props) {
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
                    action={`/${teamSlug}/savings/recipients`}
                    method="post"
                    className="space-y-6"
                    onSuccess={() => onOpenChange(false)}
                >
                    {({ processing }) => (
                        <>
                            <DialogHeader>
                                <DialogTitle>Add recipient</DialogTitle>
                                <DialogDescription>
                                    People and organizations that receive
                                    payments from your savings.
                                </DialogDescription>
                            </DialogHeader>

                            <div className="grid gap-2">
                                <Label htmlFor="type">Type</Label>
                                <select
                                    id="type"
                                    name="type"
                                    className="h-9 rounded-md border border-input px-3 text-sm"
                                >
                                    {recipientTypes.map((type) => (
                                        <option
                                            key={type.value}
                                            value={type.value}
                                        >
                                            {type.label}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="name">Name</Label>
                                <Input id="name" name="name" required />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="notes">Notes</Label>
                                <Input id="notes" name="notes" />
                            </div>

                            <DialogFooter className="gap-2">
                                <DialogClose asChild>
                                    <Button type="button" variant="secondary">
                                        Cancel
                                    </Button>
                                </DialogClose>
                                <Button type="submit" disabled={processing}>
                                    Add recipient
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
