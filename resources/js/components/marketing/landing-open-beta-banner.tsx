import { Link, usePage } from '@inertiajs/react';
import { Sparkles } from 'lucide-react';
/* @chisel-registration */
import { BETA_FREE_MESSAGE } from '@/lib/beta-copy';
import { register } from '@/routes';
/* @end-chisel-registration */
import type { SharedData } from '@/types';

export default function LandingOpenBetaBanner() {
    const { openBeta } = usePage<SharedData>().props;

    if (!openBeta.isActive) {
        return null;
    }

    return (
        <div className="w-full border-b border-primary-foreground/10 bg-primary text-center text-primary-foreground">
            <div className="flex w-full flex-col items-center justify-center gap-2 px-6 py-3 lg:flex-row lg:flex-wrap lg:gap-x-5 lg:gap-y-2 lg:px-12 lg:py-3.5">
                <div className="flex items-center justify-center gap-2 text-sm font-medium">
                    <Sparkles className="size-4 shrink-0" aria-hidden />
                    <span>Open beta — free access</span>
                </div>
                <p className="max-w-4xl text-sm leading-relaxed text-primary-foreground/90">
                    {BETA_FREE_MESSAGE}
                </p>
                {/* @chisel-registration */}
                <Link
                    href={register()}
                    className="shrink-0 text-sm font-semibold underline underline-offset-4 hover:text-primary-foreground/80"
                >
                    Create beta account
                </Link>
                {/* @end-chisel-registration */}
            </div>
        </div>
    );
}
