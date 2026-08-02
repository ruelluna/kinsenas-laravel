import { Link, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
/* @chisel-registration */
import { register } from '@/routes';
/* @end-chisel-registration */
import type { SharedData } from '@/types';

type LandingFinalCtaProps = {
    showCta: boolean;
};

export default function LandingFinalCta({ showCta }: LandingFinalCtaProps) {
    const { openBeta } = usePage<SharedData>().props;

    return (
        <section className="px-6 pt-4 pb-20 lg:px-10 lg:pb-28">
            <div className="relative mx-auto max-w-4xl overflow-hidden rounded-3xl border border-primary/15 bg-gradient-to-br from-primary/8 via-background to-success/8 px-8 py-14 text-center lg:px-14 lg:py-16">
                <div
                    className="pointer-events-none absolute -top-10 -right-10 size-40 rounded-full bg-warning/10 blur-2xl"
                    aria-hidden
                />
                <div className="relative space-y-6">
                    <h2 className="text-3xl font-semibold tracking-tight text-balance text-foreground lg:text-4xl">
                        Make your next payday feel different.
                    </h2>
                    <p className="mx-auto max-w-lg text-lg leading-relaxed text-pretty text-muted-foreground">
                        {openBeta.isActive
                            ? 'Apply for the free public beta. Verify your email, get approved, and start planning with real data.'
                            : 'Start with one income. Split it. Set it aside. See what changes.'}
                    </p>
                    {showCta && (
                        <Button
                            size="lg"
                            className="h-11 rounded-full px-8"
                            asChild
                        >
                            {/* @chisel-registration */}
                            <Link href={register()}>
                                Create My Kinsenas Plan
                            </Link>
                            {/* @end-chisel-registration */}
                        </Button>
                    )}
                </div>
            </div>
        </section>
    );
}
