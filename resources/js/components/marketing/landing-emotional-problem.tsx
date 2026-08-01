import { PAIN_POINTS } from '@/components/marketing/landing-content';
import LandingSection, {
    LandingSectionHeader,
} from '@/components/marketing/landing-section';

export default function LandingEmotionalProblem() {
    return (
        <LandingSection tone="soft">
            <LandingSectionHeader
                title="Hindi ka naman maluho. Wala ka lang sistema."
                description="You try to save. You promise yourself this payday will be different. But when money sits in one place, everything feels spendable. Kinsenas helps you decide first, so you are not relying on guilt, memory, or willpower."
            />
            <ul className="grid gap-3 sm:grid-cols-2">
                {PAIN_POINTS.map((point) => (
                    <li
                        key={point}
                        className="rounded-2xl border border-border/40 bg-background/70 px-5 py-4 text-sm leading-relaxed text-foreground"
                    >
                        {point}
                    </li>
                ))}
            </ul>
        </LandingSection>
    );
}
