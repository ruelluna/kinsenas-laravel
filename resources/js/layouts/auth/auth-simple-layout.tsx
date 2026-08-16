import { Link } from '@inertiajs/react';
import { NavigationLoadingOverlay } from '@/components/navigation/navigation-loading-overlay';
import { InstallAppAuthPrompt } from '@/components/pwa/install-app-auth-prompt';
import { PwaInstallLayout } from '@/components/pwa/pwa-install-layout';
import { PwaInstallSheetHost } from '@/components/pwa/pwa-install-sheet-host';
import ThemeToggle from '@/components/theme-toggle';
import { KINSENAS_HORIZONTAL_LOGO } from '@/lib/brand';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <PwaInstallLayout>
            <div className="relative flex min-h-svh flex-col items-center justify-center gap-6 bg-midnight p-6 font-dm text-foreground selection:bg-primary/30 md:p-10">
                <div
                    aria-hidden
                    className="pointer-events-none absolute -top-24 -left-32 h-80 w-80 rounded-full bg-teal/10 blur-[100px]"
                />
                <div
                    aria-hidden
                    className="pointer-events-none absolute top-40 right-0 h-72 w-72 rounded-full bg-gold/10 blur-[110px]"
                />

                <ThemeToggle
                    data-test="auth-theme-toggle"
                    className="absolute top-6 right-6"
                />

                <NavigationLoadingOverlay reserveBottomNav={false} />

                <div className="relative w-full max-w-sm">
                    <div className="flex flex-col gap-8">
                        <div className="flex flex-col items-center gap-4">
                            <Link
                                href={home()}
                                className="flex flex-col items-center gap-2 transition-opacity hover:opacity-90"
                            >
                                <span className="inline-flex dark:rounded-xl dark:bg-white dark:px-2.5 dark:py-1.5 dark:shadow-sm dark:ring-1 dark:ring-white/15">
                                    <img
                                        src={KINSENAS_HORIZONTAL_LOGO}
                                        alt="Kinsenas"
                                        className="h-12 w-auto max-w-[min(100%,16rem)] object-contain sm:h-14 dark:h-10 dark:max-w-[min(100%,14rem)] dark:sm:h-11"
                                    />
                                </span>
                                <span className="sr-only">{title}</span>
                            </Link>

                            <div className="space-y-2 text-center">
                                <h1 className="font-space text-2xl font-bold text-foreground">
                                    {title}
                                </h1>
                                <p className="text-center text-sm leading-relaxed text-muted-foreground">
                                    {description}
                                </p>
                            </div>
                        </div>

                        <InstallAppAuthPrompt />

                        <div className="rounded-3xl border border-border bg-surface p-6 shadow-sm md:p-8">
                            {children}
                        </div>
                    </div>
                </div>

                <PwaInstallSheetHost />
            </div>
        </PwaInstallLayout>
    );
}
