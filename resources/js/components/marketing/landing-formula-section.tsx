import {
    ALLOCATION_BG_CLASSES,
    ALLOCATION_TINT_CLASSES,
    bucketAmount,
    FORMULA_BUCKETS,
    SAMPLE_INCOME,
} from '@/components/marketing/landing-content';
import LandingSection, {
    LandingSectionHeader,
} from '@/components/marketing/landing-section';
import { formatMoney } from '@/lib/format-money';
import { cn } from '@/lib/utils';

export default function LandingFormulaSection() {
    return (
        <LandingSection tone="inset">
            <LandingSectionHeader
                eyebrow="Hindi lang budget. Sistema sa sweldo."
                title="Use a formula, or make your own."
                description="Start with familiar saving methods like TRC or Abundant-style splits, then adjust them to match your real life."
            />

            <div className="rounded-3xl border border-border/40 bg-card/60 p-6 backdrop-blur-sm lg:p-8">
                <div className="mb-8 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-sm text-muted-foreground">
                            Sample income
                        </p>
                        <p className="text-2xl font-semibold tracking-tight text-foreground">
                            {formatMoney(SAMPLE_INCOME)}
                        </p>
                    </div>
                    <p className="text-sm text-muted-foreground">
                        Six buckets, one payday
                    </p>
                </div>

                <div className="mb-8 flex h-3 gap-1 overflow-hidden rounded-full bg-muted/50 p-0.5">
                    {FORMULA_BUCKETS.map((bucket) => (
                        <div
                            key={bucket.label}
                            className={cn(
                                'h-full min-w-0 rounded-full',
                                ALLOCATION_BG_CLASSES[bucket.colorIndex],
                            )}
                            style={{ width: `${bucket.percentage}%` }}
                            title={`${bucket.label}: ${bucket.percentage}%`}
                        />
                    ))}
                </div>

                <ul className="grid gap-2 sm:grid-cols-2 lg:gap-3">
                    {FORMULA_BUCKETS.map((bucket) => (
                        <li
                            key={bucket.label}
                            className={cn(
                                'flex items-center justify-between gap-4 rounded-xl px-4 py-3.5',
                                ALLOCATION_TINT_CLASSES[bucket.colorIndex],
                            )}
                        >
                            <div className="min-w-0">
                                <p className="text-sm font-medium text-foreground">
                                    {bucket.label}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    {bucket.percentage}% of income
                                </p>
                            </div>
                            <p className="shrink-0 text-sm font-semibold tabular-nums text-foreground">
                                {formatMoney(
                                    bucketAmount(
                                        SAMPLE_INCOME,
                                        bucket.percentage,
                                    ),
                                )}
                            </p>
                        </li>
                    ))}
                </ul>
            </div>
        </LandingSection>
    );
}
