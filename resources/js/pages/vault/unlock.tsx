import { Form, Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Props = { isLocked: boolean };

export default function VaultUnlock({ isLocked }: Props) {
    return (
        <>
            <Head title="Unlock vault" />
            <div className="safe-area-x mx-auto flex max-w-md flex-col gap-6 p-4 pb-[calc(1rem+env(safe-area-inset-bottom))]">
                <div>
                    <h1 className="font-space text-xl font-bold text-foreground">
                        Unlock your financial vault
                    </h1>
                    <p className="mt-2 text-sm text-muted-foreground">
                        {isLocked
                            ? 'Your vault was locked after a password reset. Enter your recovery key to access amounts.'
                            : 'Enter your password to decrypt your savings data for this session.'}
                    </p>
                </div>
                <Form
                    action="/vault/unlock"
                    method="post"
                    className="space-y-4"
                >
                    {!isLocked && (
                        <div className="grid gap-2">
                            <Label htmlFor="password">Password</Label>
                            <Input
                                id="password"
                                name="password"
                                type="password"
                                required={!isLocked}
                            />
                        </div>
                    )}
                    {isLocked && (
                        <div className="grid gap-2">
                            <Label htmlFor="recovery_key">Recovery key</Label>
                            <Input
                                id="recovery_key"
                                name="recovery_key"
                                required={isLocked}
                            />
                        </div>
                    )}
                    <Button type="submit" className="h-11 w-full font-bold">
                        Unlock
                    </Button>
                </Form>
            </div>
        </>
    );
}
