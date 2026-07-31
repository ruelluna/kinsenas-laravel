import { Head, Link, usePage } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
import { Button } from '@/components/ui/button';
import { dashboard, login } from '@/routes';
/* @chisel-registration */
import { register } from '@/routes';
/* @end-chisel-registration */

export default function Welcome() {
    const { auth, currentTeam, name } = usePage().props;
    const dashboardUrl = currentTeam ? dashboard(currentTeam.slug) : '/';

    return (
        <>
            <Head title="Welcome" />
            <div className="flex min-h-screen flex-col bg-background">
                <header className="flex items-center justify-between px-6 py-4 lg:px-10">
                    <div className="flex items-center gap-2">
                        <div className="flex size-9 items-center justify-center rounded-md bg-primary text-primary-foreground">
                            <AppLogoIcon className="size-5 fill-current" />
                        </div>
                        <span className="text-sm font-semibold">{name}</span>
                    </div>
                    <nav className="flex items-center gap-2">
                        {auth.user ? (
                            <Button asChild>
                                <Link href={dashboardUrl}>Dashboard</Link>
                            </Button>
                        ) : (
                            <>
                                <Button variant="ghost" asChild>
                                    <Link href={login()}>Log in</Link>
                                </Button>
                                {/* @chisel-registration */}
                                <Button asChild>
                                    <Link href={register()}>Register</Link>
                                </Button>
                                {/* @end-chisel-registration */}
                            </>
                        )}
                    </nav>
                </header>

                <main className="flex flex-1 flex-col items-center justify-center px-6 pb-16 text-center lg:px-10">
                    <div className="max-w-2xl space-y-6">
                        <h1 className="text-4xl font-semibold tracking-tight text-foreground lg:text-5xl">
                            Plan, track, and grow your savings
                        </h1>
                        <p className="text-lg text-muted-foreground">
                            FutureSave helps you allocate income across funds, record transfers and spending, and stay on top of your financial goals.
                        </p>
                        {!auth.user && (
                            <div className="flex flex-wrap items-center justify-center gap-3 pt-2">
                                <Button size="lg" asChild>
                                    <Link href={register()}>Get started</Link>
                                </Button>
                                <Button size="lg" variant="outline" asChild>
                                    <Link href={login()}>Log in</Link>
                                </Button>
                            </div>
                        )}
                    </div>
                </main>

                <footer className="border-t px-6 py-4 text-center text-sm text-muted-foreground lg:px-10">
                    Built for thoughtful money management.
                </footer>
            </div>
        </>
    );
}
