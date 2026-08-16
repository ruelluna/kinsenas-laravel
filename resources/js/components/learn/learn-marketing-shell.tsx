import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import LandingHeader from '@/components/marketing/landing-header';
import { Button } from '@/components/ui/button';

type Props = {
    children: ReactNode;
    isAuthenticated: boolean;
    dashboardUrl?: string;
};

export default function LearnMarketingShell({
    children,
    isAuthenticated,
    dashboardUrl = '/',
}: Props) {
    return (
        <div className="landing-marketing min-h-screen bg-midnight font-dm text-foreground selection:bg-primary/30">
            <LandingHeader
                isAuthenticated={isAuthenticated}
                dashboardUrl={dashboardUrl}
            />
            <main className="mx-auto max-w-4xl px-4 py-10 sm:px-6">{children}</main>
            <footer className="border-t border-white/10 px-4 py-8 text-center text-sm text-muted-foreground">
                <Link href="/" className="hover:text-foreground">
                    Kinsenas
                </Link>
                {' · '}
                <Link href="/learn" className="hover:text-foreground">
                    Learn
                </Link>
                {!isAuthenticated && (
                    <>
                        {' · '}
                        <Button variant="link" className="h-auto p-0" asChild>
                            <Link href="/register">Create account</Link>
                        </Button>
                    </>
                )}
            </footer>
        </div>
    );
}
