import {
    HERO_DEMO_BUCKETS,
    SAMPLE_INCOME,
    bucketAmount,
} from '@/components/marketing/landing-content';
import { landingDemoBucketClassName } from '@/components/marketing/landing-surface';
import { formatMoney } from '@/lib/format-money';

export default function LandingHeroDemoCard() {
    const billsAmount = bucketAmount(SAMPLE_INCOME, 45);
    const goalsAmount = bucketAmount(SAMPLE_INCOME, 10);

    return (
        <div className="relative">
            <div className="absolute -inset-4 rounded-full bg-glow/8 blur-3xl" />
            <div className="relative rounded-3xl border border-border bg-surface p-8 shadow-2xl">
                <div className="absolute inset-x-8 -top-px h-px bg-gradient-to-r from-transparent via-gold/60 to-transparent" />

                <div className="mb-8 flex items-end justify-between">
                    <div>
                        <p className="mb-1 text-xs font-bold tracking-widest text-muted-foreground uppercase">
                            Available Balance
                        </p>
                        <h2 className="font-space text-4xl font-bold text-foreground">
                            {formatMoney(SAMPLE_INCOME)}
                        </h2>
                    </div>
                    <div className="text-right text-xs text-muted-foreground">
                        Oct 15 Payday
                    </div>
                </div>

                <div className="space-y-5">
                    <div className="space-y-2">
                        <div className="flex justify-between text-sm text-foreground">
                            <span className="font-medium">
                                Essential Bills (45%)
                            </span>
                            <span>{formatMoney(billsAmount)}</span>
                        </div>
                        <div className="h-2 overflow-hidden rounded-full bg-midnight">
                            <div className="h-full w-[45%] rounded-full bg-primary" />
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        {HERO_DEMO_BUCKETS.map((bucket) => (
                            <div
                                key={bucket.label}
                                className={landingDemoBucketClassName}
                            >
                                <span
                                    className={`absolute inset-x-0 top-0 h-0.5 ${bucket.accentClass} opacity-80`}
                                />
                                <p className="mb-1 text-[10px] tracking-tighter text-muted-foreground uppercase">
                                    {bucket.label}
                                </p>
                                <p
                                    className={`font-space font-bold ${bucket.textClass}`}
                                >
                                    {formatMoney(bucket.amount)}
                                </p>
                            </div>
                        ))}
                    </div>

                    <div className="flex items-center justify-between border-t border-border pt-4">
                        <span className="text-xs text-muted-foreground">
                            Remaining: Goals (10%)
                        </span>
                        <span className="text-xs font-bold text-foreground">
                            {formatMoney(goalsAmount)}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    );
}
