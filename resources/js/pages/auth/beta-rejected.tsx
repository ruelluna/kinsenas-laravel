import { Head } from '@inertiajs/react';
import TextLink from '@/components/text-link';
import { logout } from '@/routes';

export default function BetaRejected() {
    return (
        <>
            <Head title="Beta application not approved" />

            <div className="space-y-4 text-center text-sm text-muted-foreground">
                <p>
                    Your open beta application was not approved at this time. If you think this was a
                    mistake, reply to our welcome email or contact support.
                </p>
            </div>

            <div className="mt-6 text-center">
                <TextLink href={logout()} className="text-sm">
                    Log out
                </TextLink>
            </div>
        </>
    );
}

BetaRejected.layout = {
    title: 'Beta application not approved',
    description: 'Your application was not approved for this beta round.',
};
