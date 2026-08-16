import { Link } from '@inertiajs/react';
/* @chisel-registration */
import { register } from '@/routes';
/* @end-chisel-registration */

type LandingFinalCtaProps = {
    showCta: boolean;
};

export default function LandingFinalCta({ showCta }: LandingFinalCtaProps) {
    return (
        <footer
            id="beta"
            className="mx-auto max-w-7xl border-t border-border px-6 py-24 text-center"
        >
            {showCta && (
                <>
                    <h2 className="mb-8 font-space text-4xl font-bold text-foreground">
                        Ready for a smarter sweldo?
                    </h2>
                    {/* @chisel-registration */}
                    <Link
                        href={register()}
                        className="inline-block rounded-2xl bg-glow px-12 py-5 text-xl font-bold text-accent-foreground transition-transform hover:scale-105"
                    >
                        Join the Open Beta
                    </Link>
                    {/* @end-chisel-registration */}
                    <p className="mt-8 text-sm italic text-muted-foreground">
                        Kinsenas is currently free while in beta. No credit
                        card required.
                    </p>
                </>
            )}

            <div
                className={`flex flex-col items-center justify-between gap-8 text-xs text-muted-foreground md:flex-row ${showCta ? 'mt-24' : 'mt-0'}`}
            >
                <p>
                    © {new Date().getFullYear()} Kinsenas. Built for the
                    Filipino saver.
                </p>
                <div className="flex gap-8">
                    <a
                        href="#"
                        className="transition-colors hover:text-primary"
                    >
                        Terms
                    </a>
                    <a
                        href="#"
                        className="transition-colors hover:text-primary"
                    >
                        Privacy
                    </a>
                    <a
                        href="#"
                        className="transition-colors hover:text-primary"
                    >
                        Support
                    </a>
                </div>
            </div>
        </footer>
    );
}
