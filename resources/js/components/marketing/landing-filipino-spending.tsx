import {
    CreditCard,
    Heart,
    PiggyBank,
    Receipt,
    ShoppingBag,
    TrendingUp,
    Users,
    Wallet,
    type LucideIcon,
} from 'lucide-react';
import {
    KINSENAS_PAYDAY_LINES,
    SAMPLE_INCOME,
    SAMPLE_SAVED_AMOUNT,
    USUAL_PAYDAY_LINES,
    type ComparisonLine,
} from '@/components/marketing/landing-content';
import { formatMoney } from '@/lib/format-money';

const ICONS: Record<string, LucideIcon> = {
    receipt: Receipt,
    users: Users,
    'credit-card': CreditCard,
    'shopping-bag': ShoppingBag,
    wallet: Wallet,
    'piggy-bank': PiggyBank,
    heart: Heart,
    'trending-up': TrendingUp,
};

function ComparisonList({ lines }: { lines: ComparisonLine[] }) {
    return (
        <ul className="space-y-5">
            {lines.map((line) => {
                const Icon = ICONS[line.icon] ?? Receipt;

                return (
                    <li key={line.label} className="flex items-start gap-4">
                        <Icon className="mt-0.5 h-5 w-5 shrink-0 text-muted-foreground" />
                        <div className="flex-1">
                            <div className="flex items-center justify-between">
                                <span className="font-medium text-foreground">{line.label}</span>
                                <span
                                    className={`font-space font-bold ${line.amountClass}`}
                                >
                                    {formatMoney(line.amount)}
                                </span>
                            </div>
                            <p className="text-sm text-muted-foreground">
                                {line.description}
                            </p>
                        </div>
                    </li>
                );
            })}
        </ul>
    );
}

export default function LandingFilipinoSpending() {
    return (
        <section id="filipino-spending" className="mx-auto max-w-7xl px-6 py-24">
            <div className="mb-16 md:flex md:items-end md:justify-between">
                <div>
                    <h2 className="mb-4 font-space text-4xl font-bold text-foreground">
                        Built for how Filipinos
                        <br />
                        actually spend.
                    </h2>
                    <p className="max-w-xl text-muted-foreground">
                        Most budgeting apps ignore the realities of Filipino
                        life. Kinsenas is designed around them.
                    </p>
                </div>
                <div className="mt-6 rounded-2xl border border-gold/25 bg-gold/5 px-6 py-4 md:mt-0">
                    <p className="text-xs font-bold tracking-widest text-gold-soft uppercase">
                        Sample {formatMoney(SAMPLE_INCOME)} sweldo
                    </p>
                    <p className="font-space text-2xl font-bold text-gold">
                        {SAMPLE_SAVED_AMOUNT} saved
                    </p>
                </div>
            </div>

            <div className="grid gap-6 md:grid-cols-2">
                <div className="rounded-3xl border border-border bg-surface p-8">
                    <div className="mb-8 flex items-center gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-full bg-muted">
                            <Wallet className="h-5 w-5 text-muted-foreground" />
                        </div>
                        <div>
                            <h3 className="font-space text-xl font-bold text-foreground">
                                The usual payday
                            </h3>
                            <p className="text-xs text-muted-foreground">
                                Reactive, no system
                            </p>
                        </div>
                    </div>
                    <ComparisonList lines={USUAL_PAYDAY_LINES} />
                </div>

                <div className="rounded-3xl border border-primary/25 bg-gradient-to-b from-surface to-primary/5 p-8">
                    <div className="mb-8 flex items-center gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-full bg-primary/15">
                            <PiggyBank className="h-5 w-5 text-primary" />
                        </div>
                        <div>
                            <h3 className="font-space text-xl font-bold text-foreground">
                                With Kinsenas
                            </h3>
                            <p className="text-xs text-muted-foreground">
                                Buckets before spending
                            </p>
                        </div>
                    </div>
                    <ComparisonList lines={KINSENAS_PAYDAY_LINES} />
                </div>
            </div>
        </section>
    );
}
