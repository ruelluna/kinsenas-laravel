import {
    bucketAmount,
    HERO_BUCKETS,
    HERO_BUCKET_CARD_CLASSES,
    SAMPLE_INCOME,
} from '@/components/marketing/landing-content';
import { formatMoney } from '@/lib/format-money';
import { cn } from '@/lib/utils';

export default function PaydaySplitVisual() {
    return (
        <div className="w-full max-w-md lg:max-w-lg">
            <div className="rounded-2xl border border-white/20 bg-black/35 p-6 shadow-sm backdrop-blur-md">
                <div className="flex items-center justify-between gap-4">
                    <div>
                        <p className="text-sm text-white/70">This payday</p>
                        <p className="mt-1 text-3xl font-semibold tracking-tight text-white">
                            {formatMoney(SAMPLE_INCOME)}
                        </p>
                    </div>
                    <span className="rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-white/90">
                        Sweldo
                    </span>
                </div>
            </div>

            <div className="flex justify-center py-3" aria-hidden>
                <div className="h-8 w-px bg-linear-to-b from-white/30 to-transparent" />
            </div>

            <div className="grid grid-cols-2 gap-2.5 sm:grid-cols-3 sm:gap-3">
                {HERO_BUCKETS.map((bucket) => {
                    const styles = HERO_BUCKET_CARD_CLASSES[bucket.colorIndex];

                    return (
                        <div
                            key={bucket.label}
                            className={cn(
                                'rounded-xl border px-3.5 py-3.5 shadow-sm backdrop-blur-sm',
                                styles.surface,
                            )}
                        >
                            <p
                                className={cn(
                                    'text-xs font-semibold tracking-wide uppercase',
                                    styles.label,
                                )}
                            >
                                {bucket.label}
                            </p>
                            <p
                                className={cn(
                                    'mt-2 text-base font-semibold tabular-nums',
                                    styles.label,
                                )}
                            >
                                {formatMoney(
                                    bucketAmount(
                                        SAMPLE_INCOME,
                                        bucket.percentage,
                                    ),
                                )}
                            </p>
                            <p className={cn('mt-0.5 text-xs', styles.muted)}>
                                {bucket.percentage}% set aside
                            </p>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}
