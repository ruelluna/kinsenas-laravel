import { Link, usePage } from '@inertiajs/react';
import LandingHeroDemoCard from '@/components/marketing/landing-hero-demo-card';
/* @chisel-registration */
import { register } from '@/routes';
/* @end-chisel-registration */
import type { SharedData } from '@/types';

type LandingHeroProps = {
    showCtas: boolean;
};

export default function LandingHero({ showCtas }: LandingHeroProps) {
    const { openBeta } = usePage<SharedData>().props;

    return (
        <section className="relative mx-auto grid max-w-7xl items-center gap-16 px-6 pt-12 pb-24 md:grid-cols-2">
            <div
                aria-hidden
                className="pointer-events-none absolute -top-24 -left-32 h-80 w-80 rounded-full bg-teal/10 blur-[100px]"
            />
            <div
                aria-hidden
                className="pointer-events-none absolute top-40 right-0 h-72 w-72 rounded-full bg-gold/10 blur-[110px]"
            />

            <div className="space-y-8">
                {openBeta.isActive && (
                    <div className="relative inline-flex items-center gap-2 rounded-full border border-gold/25 bg-gold/10 px-3 py-1 text-xs font-bold tracking-widest text-gold-soft uppercase">
                        <span className="relative flex h-2 w-2">
                            <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-gold opacity-75" />
                            <span className="relative inline-flex h-2 w-2 rounded-full bg-gold" />
                        </span>
                        Now in Open Beta
                    </div>
                )}

                <h1 className="relative font-space text-6xl leading-[0.9] font-bold tracking-tighter text-foreground md:text-7xl">
                    Sweldo with
                    <br />
                    <span className="bg-gradient-to-r from-primary via-glow to-teal bg-clip-text text-transparent italic">
                        a plan.
                    </span>
                </h1>

                <p className="relative max-w-md text-xl leading-relaxed text-muted-foreground">
                    The Filipino payday planning app designed to end the “petsa de
                    peligro” cycle. Direct your PHP earnings into buckets before
                    you spend a single cent.
                </p>

                {showCtas && (
                    <div className="relative flex flex-wrap items-center gap-4 pt-4">
                        {/* @chisel-registration */}
                        <Link
                            href={register()}
                            className="rounded-xl bg-primary px-8 py-4 text-lg font-bold text-primary-foreground transition-all hover:shadow-[0_0_20px_color-mix(in_oklab,var(--primary)_40%,transparent)]"
                        >
                            Start Planning Free
                        </Link>
                        {/* @end-chisel-registration */}
                        <a
                            href="#formulas"
                            className="rounded-xl border border-gold/30 px-8 py-4 text-lg font-bold text-gold-soft transition-colors hover:bg-gold/10"
                        >
                            See the formulas
                        </a>
                    </div>
                )}
            </div>

            <LandingHeroDemoCard />
        </section>
    );
}
