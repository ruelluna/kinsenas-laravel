import { Head, usePage } from '@inertiajs/react';
import TextLink from '@/components/text-link';
import { BETA_FREE_MESSAGE } from '@/lib/beta-copy';
import { logout } from '@/routes';
import type { SharedData } from '@/types';

export default function BetaPending() {
    return (
        <>
            <Head title="Beta application pending" />

            <div className="space-y-4 text-center text-sm text-muted-foreground">
                <p>
                    Thanks for applying to the Kinsenas public beta. Your application is waiting for
                    admin review.
                </p>
                <p>
                    Once approved, you can sign in with your real account and use the core savings
                    planner for free. We will email you when your access is ready.
                </p>
                <p className="text-pretty">{BETA_FREE_MESSAGE}</p>
            </div>

            <div className="mt-6 text-center">
                <TextLink href={logout()} className="text-sm">
                    Log out
                </TextLink>
            </div>
        </>
    );
}

BetaPending.layout = {
    title: 'Beta application pending',
    description: 'We are reviewing your public beta application.',
};
