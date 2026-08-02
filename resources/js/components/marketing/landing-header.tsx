import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { KINSENAS_HORIZONTAL_LOGO } from '@/lib/brand';
import { home, login } from '@/routes';
/* @chisel-registration */
import { register } from '@/routes';
/* @end-chisel-registration */

type LandingHeaderProps = {
    isAuthenticated: boolean;
    dashboardUrl: string;
    appName: string;
};

export default function LandingHeader({
    isAuthenticated,
    dashboardUrl,
    appName,
}: LandingHeaderProps) {
    return (
        <header className="border-b border-border/40 bg-background/75 backdrop-blur-md supports-[backdrop-filter]:bg-background/60">
            <div className="mx-auto flex max-w-6xl items-center justify-between px-6 py-3.5 lg:px-10">
                <Link
                    href={home()}
                    className="flex items-center transition-opacity hover:opacity-80"
                >
                    <img
                        src={KINSENAS_HORIZONTAL_LOGO}
                        alt={appName}
                        className="h-11 w-auto max-w-[min(100%,14rem)] object-contain sm:h-14"
                    />
                </Link>
                <nav className="flex items-center gap-1.5 sm:gap-2">
                    {isAuthenticated ? (
                        <Button className="rounded-full" asChild>
                            <Link href={dashboardUrl}>Dashboard</Link>
                        </Button>
                    ) : (
                        <>
                            <Button
                                variant="ghost"
                                className="rounded-full"
                                asChild
                            >
                                <Link href={login()}>Log in</Link>
                            </Button>
                            {/* @chisel-registration */}
                            <Button className="rounded-full" asChild>
                                <Link href={register()}>Get started</Link>
                            </Button>
                            {/* @end-chisel-registration */}
                        </>
                    )}
                </nav>
            </div>
        </header>
    );
}
