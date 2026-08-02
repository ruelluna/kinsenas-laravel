import { Form } from '@inertiajs/react';
import { useState } from 'react';
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
import type { AdminPlatformUser } from '@/types/billing';

type Props = {
    user: AdminPlatformUser;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export default function DeletePlatformUserModal({
    user,
    open,
    onOpenChange,
}: Props) {
    const [confirmationEmail, setConfirmationEmail] = useState('');

    const canDeleteUser = confirmationEmail === user.email;

    const handleOpenChange = (nextOpen: boolean) => {
        onOpenChange(nextOpen);

        if (!nextOpen) {
            setConfirmationEmail('');
        }
    };

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogContent>
                <Form
                    key={String(open)}
                    action={`/admin/platform-users/${user.id}`}
                    method="delete"
                    className="space-y-6"
                    onSuccess={() => handleOpenChange(false)}
                >
                    {({ errors, processing }) => (
                        <>
                            <DialogHeader>
                                <DialogTitle>Remove user account?</DialogTitle>
                                <DialogDescription>
                                    This permanently deletes{' '}
                                    <strong>{user.name}</strong> ({user.email}),
                                    their personal workspace, subscription data,
                                    and vault. This cannot be undone.
                                </DialogDescription>
                            </DialogHeader>

                            <div className="space-y-4 py-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="confirmation-email">
                                        Type <strong>{user.email}</strong> to
                                        confirm
                                    </Label>
                                    <Input
                                        id="confirmation-email"
                                        name="email"
                                        data-test="delete-user-email"
                                        type="email"
                                        value={confirmationEmail}
                                        onChange={(event) =>
                                            setConfirmationEmail(
                                                event.target.value,
                                            )
                                        }
                                        placeholder="Enter email address"
                                        autoComplete="off"
                                    />
                                    <InputError message={errors.email} />
                                </div>
                            </div>

                            <DialogFooter className="gap-2">
                                <DialogClose asChild>
                                    <Button variant="secondary">Cancel</Button>
                                </DialogClose>

                                <Button
                                    variant="destructive"
                                    type="submit"
                                    data-test="delete-user-confirm"
                                    disabled={!canDeleteUser || processing}
                                >
                                    Remove user
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
