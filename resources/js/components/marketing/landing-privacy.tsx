import { Check, Lock, ShieldCheck } from 'lucide-react';
import { ENCRYPTION_TRUST_POINTS } from '@/components/marketing/landing-content';
import LandingSection from '@/components/marketing/landing-section';

export default function LandingPrivacy() {
    return (
        <LandingSection id="privacy">
            <div className="mx-auto grid max-w-4xl items-start gap-10 rounded-3xl border border-border/40 bg-muted/20 p-8 lg:grid-cols-[auto_1fr] lg:gap-12 lg:p-10">
                <div className="flex size-14 items-center justify-center rounded-2xl bg-primary/10 text-primary lg:size-16">
                    <Lock className="size-6 lg:size-7" aria-hidden />
                </div>
                <div className="space-y-6">
                    <div className="space-y-4">
                        <div className="flex flex-wrap items-center gap-2">
                            <ShieldCheck
                                className="size-4 text-success"
                                aria-hidden
                            />
                            <p className="text-sm font-medium text-success">
                                Encrypted by default
                            </p>
                        </div>
                        <h2 className="text-balance text-2xl font-semibold tracking-tight text-foreground lg:text-3xl">
                            Your income stays yours — even from us.
                        </h2>
                        <p className="text-pretty text-base leading-relaxed text-muted-foreground lg:text-lg">
                            Kinsenas is built so your payday amounts are private
                            and protected. We encrypt your financial data so
                            nobody on our team can browse your income, savings
                            splits, or transfers — only you can, when you
                            unlock your vault.
                        </p>
                    </div>
                    <ul className="space-y-3">
                        {ENCRYPTION_TRUST_POINTS.map((point) => (
                            <li
                                key={point}
                                className="flex items-start gap-3 text-sm leading-relaxed text-foreground"
                            >
                                <span className="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-success/15">
                                    <Check
                                        className="size-3 text-success"
                                        aria-hidden
                                    />
                                </span>
                                {point}
                            </li>
                        ))}
                    </ul>
                </div>
            </div>
        </LandingSection>
    );
}
