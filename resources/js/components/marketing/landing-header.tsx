import { Link, usePage } from '@inertiajs/react';
import LandingThemeToggle from '@/components/marketing/landing-theme-toggle';
import { KINSENAS_HORIZONTAL_LOGO } from '@/lib/brand';
import { home, login } from '@/routes';
/* @chisel-registration */
import { register } from '@/routes';
/* @end-chisel-registration */
import type { SharedData } from '@/types';

type LandingHeaderProps = {
    isAuthenticated: boolean;
    dashboardUrl: string;
};

const NAV_LINKS = [
    { href: '#loop', label: 'How it works' },
    { href: '#filipino-spending', label: 'For Filipinos' },
    { href: '#banks', label: 'Banks' },
    { href: '/learn', label: 'Learn' },
    { href: '#security', label: 'Security' },
] as const;

export default function LandingHeader({
    isAuthenticated,
    dashboardUrl,
}: LandingHeaderProps) {
    const { name: appName } = usePage<SharedData>().props;

    return (
        <nav className="mx-auto flex max-w-7xl items-center justify-between px-6 py-6">
            <Link
                href={home()}
                className="flex items-center transition-opacity hover:opacity-90"
            >
                <span className="inline-flex dark:rounded-xl dark:bg-white dark:px-2.5 dark:py-1.5 dark:shadow-sm dark:ring-1 dark:ring-white/15">
                    <img
                        src={KINSENAS_HORIZONTAL_LOGO}
                        alt={appName}
                        className="h-10 w-auto max-w-[min(100%,14rem)] object-contain sm:h-11 dark:h-8 dark:max-w-[min(100%,12rem)] dark:sm:h-9"
                    />
                </span>
            </Link>

            <div className="hidden gap-8 text-sm font-medium text-muted-foreground md:flex">
                {NAV_LINKS.map((link) =>
                    link.href.startsWith('/') ? (
                        <Link
                            key={link.href}
                            href={link.href}
                            className="transition-colors hover:text-glow"
                        >
                            {link.label}
                        </Link>
                    ) : (
                        <a
                            key={link.href}
                            href={link.href}
                            className="transition-colors hover:text-glow"
                        >
                            {link.label}
                        </a>
                    ),
                )}
            </div>

            <div className="flex items-center gap-3">
                <LandingThemeToggle />
                {isAuthenticated ? (
                    <Link
                        href={dashboardUrl}
                        className="rounded-full bg-primary px-5 py-2.5 text-sm font-bold text-primary-foreground transition-all hover:bg-glow"
                    >
                        Dashboard
                    </Link>
                ) : (
                    <>
                        <Link
                            href={login()}
                            data-test="landing-login-link"
                            className="text-sm font-medium text-muted-foreground transition-colors hover:text-glow"
                        >
                            Log in
                        </Link>
                        {/* @chisel-registration */}
                        <Link
                            href={register()}
                            className="rounded-full bg-primary px-5 py-2.5 text-sm font-bold text-primary-foreground transition-all hover:bg-glow"
                        >
                            Join Beta
                        </Link>
                        {/* @end-chisel-registration */}
                    </>
                )}
            </div>
        </nav>
    );
}
