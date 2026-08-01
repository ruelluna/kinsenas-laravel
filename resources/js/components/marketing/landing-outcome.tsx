import { Check } from 'lucide-react';
import { OUTCOME_BULLETS } from '@/components/marketing/landing-content';
import LandingSection, {
    LandingSectionHeader,
} from '@/components/marketing/landing-section';

export default function LandingOutcome() {
    return (
        <LandingSection tone="soft">
            <LandingSectionHeader
                title="Less guilt. More clarity."
                description="Kinsenas will not magically make income bigger. But it helps you stop guessing, stop wondering where everything went, and start seeing progress every payday."
            />
            <ul className="grid gap-3 sm:grid-cols-2">
                {OUTCOME_BULLETS.map((bullet) => (
                    <li
                        key={bullet}
                        className="flex items-start gap-3.5 rounded-2xl bg-background/70 px-5 py-4"
                    >
                        <span className="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-success/15">
                            <Check
                                className="size-3 text-success"
                                aria-hidden
                            />
                        </span>
                        <span className="text-sm leading-relaxed text-foreground">
                            {bullet}
                        </span>
                    </li>
                ))}
            </ul>
        </LandingSection>
    );
}
