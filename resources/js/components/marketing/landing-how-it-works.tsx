import { LOOP_STEPS } from '@/components/marketing/landing-content';
import { KINSENAS_APP_PREVIEW } from '@/lib/brand';

export default function LandingHowItWorks() {
    return (
        <section id="loop" className="mx-auto max-w-7xl border-t border-border px-6 py-24">
            <div className="grid gap-20 md:grid-cols-2">
                <div>
                    <h2 className="mb-12 font-space text-4xl font-bold text-foreground">
                        The Kinsenas Loop
                    </h2>
                    <div className="relative space-y-12">
                        <div className="absolute top-2 bottom-2 left-4 w-px bg-border" />
                        {LOOP_STEPS.map((step) => (
                            <div key={step.number} className="relative pl-12">
                                <div
                                    className={`absolute top-0 left-0 flex h-8 w-8 items-center justify-center rounded-full font-bold ${step.badgeClass}`}
                                >
                                    {step.number}
                                </div>
                                <h3 className="mb-1 font-bold text-foreground">{step.title}</h3>
                                <p className="text-sm text-muted-foreground">
                                    {step.description}
                                </p>
                            </div>
                        ))}
                    </div>
                </div>

                <img
                    src={KINSENAS_APP_PREVIEW}
                    alt="Kinsenas app showing fund buckets and recent spending in Philippine pesos"
                    width={800}
                    height={1200}
                    loading="lazy"
                    className="aspect-[4/5] w-full rounded-[1.4rem] object-cover"
                />
            </div>
        </section>
    );
}
