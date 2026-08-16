import { FORMULA_CARDS } from '@/components/marketing/landing-content';

export default function LandingFormulaSection() {
    return (
        <section id="formulas" className="mx-auto max-w-7xl border-t border-border px-6 py-24">
            <div className="mb-16">
                <h2 className="mb-4 font-space text-4xl font-bold text-foreground">
                    Choose your blueprint.
                </h2>
                <p className="text-muted-foreground">
                    Start with a proven financial framework or build your own.
                </p>
            </div>

            <div className="grid gap-6 md:grid-cols-3">
                {FORMULA_CARDS.map((card) => (
                    <div
                        key={card.title}
                        className={`group rounded-2xl border border-border bg-surface p-8 transition-colors ${card.hoverBorder}`}
                    >
                        <h3
                            className={`mb-4 text-xl font-bold text-foreground transition-colors ${card.hoverTitle}`}
                        >
                            {card.title}
                        </h3>
                        <p className="mb-6 text-sm text-muted-foreground">
                            {card.description}
                        </p>
                        <ul className="space-y-2 text-xs">
                            {card.lines.map((line) => (
                                <li
                                    key={line.label}
                                    className="flex items-center justify-between"
                                >
                                    <span className="text-foreground">{line.label}</span>
                                    <span
                                        className={`rounded-full px-2 py-0.5 font-bold ${card.badgeClass}`}
                                    >
                                        {line.pct}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    </div>
                ))}

                <div className="flex flex-col items-center justify-center rounded-2xl border border-lilac/25 bg-lilac/5 p-8 text-center transition-colors hover:border-lilac/50">
                    <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-full border border-dashed border-lilac/40">
                        <span className="text-2xl text-lilac">+</span>
                    </div>
                    <h3 className="text-xl font-bold text-lilac">Custom Plan</h3>
                    <p className="px-4 text-xs text-muted-foreground">
                        Tailor buckets to your specific needs and priorities.
                    </p>
                </div>
            </div>
        </section>
    );
}
