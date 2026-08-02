import { Link, usePage } from '@inertiajs/react';
import HeroParallaxBackground from '@/components/marketing/hero-parallax-background';
import PaydaySplitVisual from '@/components/marketing/payday-split-visual';
import { Button } from '@/components/ui/button';
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
        <section className="relative min-h-[60dvh] overflow-hidden">
            <HeroParallaxBackground />

            <div className="relative z-10 flex min-h-[60dvh] items-center px-6 py-20 lg:px-10 lg:py-28">
                <div className="mx-auto grid w-full max-w-6xl items-center gap-10 lg:grid-cols-[1.05fr_0.95fr] lg:gap-14">
                    <div className="space-y-6 p-2 text-center sm:p-4 lg:text-left">
                        <p className="inline-flex rounded-full bg-white/10 px-3.5 py-1 text-sm font-medium text-white/90 backdrop-blur-sm">
                            {openBeta.isActive
                                ? 'Public beta — free'
                                : 'Spend only after you have saved.'}
                        </p>
                        <h1 className="text-4xl leading-[1.1] font-semibold tracking-tight text-balance text-white sm:text-5xl lg:text-[3.25rem]">
                            Not how big you save —
                            <br />
                            but the habit of saving!
                        </h1>
                        <p className="mx-auto max-w-xl text-lg leading-relaxed text-pretty text-white/80 lg:mx-0">
                            {openBeta.isActive
                                ? 'Create a real account, use the core savings planner for free during beta, and keep your data when paid plans launch. Pricing: coming soon.'
                                : 'Kinsenas helps you split every income into clear portions, so your money already has a purpose before life starts asking for it.'}
                        </p>
                        {showCtas && (
                            <div className="flex flex-wrap items-center justify-center gap-3 pt-1 lg:justify-start">
                                {/* @chisel-registration */}
                                <Button
                                    size="lg"
                                    className="h-11 rounded-full px-7 shadow-sm"
                                    asChild
                                >
                                    <Link href={register()}>
                                        Create My Kinsenas Plan
                                    </Link>
                                </Button>
                                {/* @end-chisel-registration */}
                                <Button
                                    size="lg"
                                    variant="outline"
                                    className="h-11 rounded-full border-white/25 bg-white/5 px-7 text-white backdrop-blur-sm hover:bg-white/10 hover:text-white"
                                    asChild
                                >
                                    <a href="#how-it-works">See How It Works</a>
                                </Button>
                            </div>
                        )}
                    </div>

                    <div className="flex justify-center lg:justify-end">
                        <PaydaySplitVisual />
                    </div>
                </div>
            </div>
        </section>
    );
}
