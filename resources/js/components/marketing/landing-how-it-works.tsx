import { HOW_IT_WORKS_STEPS } from '@/components/marketing/landing-content';
import LandingSection, {
    LandingSectionHeader,
} from '@/components/marketing/landing-section';

export default function LandingHowItWorks() {
    return (
        <LandingSection id="how-it-works">
            <LandingSectionHeader title="Give every peso a place before it disappears." />
            <ol className="grid gap-8 md:grid-cols-3 md:gap-6 lg:gap-10">
                {HOW_IT_WORKS_STEPS.map((step, index) => (
                    <li key={step.title} className="relative flex flex-col gap-4">
                        {index < HOW_IT_WORKS_STEPS.length - 1 && (
                            <div
                                className="pointer-events-none absolute left-5 top-10 hidden h-px w-[calc(100%+1.5rem)] bg-border/60 md:block lg:w-[calc(100%+2.5rem)]"
                                aria-hidden
                            />
                        )}
                        <div className="flex size-10 items-center justify-center rounded-full border border-primary/20 bg-primary/8 text-sm font-semibold text-primary">
                            {index + 1}
                        </div>
                        <div className="space-y-2">
                            <h3 className="text-lg font-semibold text-foreground">
                                {step.title}
                            </h3>
                            <p className="text-base leading-relaxed text-muted-foreground">
                                {step.description}
                            </p>
                        </div>
                    </li>
                ))}
            </ol>
        </LandingSection>
    );
}
