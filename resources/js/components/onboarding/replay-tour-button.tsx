import { usePage } from '@inertiajs/react';
import { Sparkles } from 'lucide-react';
import { startOnboardingTour } from '@/components/onboarding/onboarding-tour-host';
import { Button } from '@/components/ui/button';
import {
    clearOnboardingTourActive,
    clearOnboardingTourCompleted,
    resetOnboardingTourAutoStartGuard,
} from '@/lib/onboarding-tour/storage';
import type { SharedData } from '@/types';

type Props = {
    variant?: 'outline' | 'ghost' | 'secondary';
    size?: 'default' | 'sm' | 'lg' | 'icon';
    className?: string;
};

export default function ReplayTourButton({
    variant = 'outline',
    size = 'sm',
    className,
}: Props) {
    const { currentTeam, subscription } = usePage<SharedData>().props;
    const teamId = currentTeam?.id;
    const teamSlug = currentTeam?.slug;
    const hasAccess = subscription?.hasAccess ?? true;

    if (!teamId || !teamSlug || !hasAccess) {
        return null;
    }

    return (
        <Button
            type="button"
            variant={variant}
            size={size}
            className={className}
            data-tour="replay-tour"
            onClick={() => {
                clearOnboardingTourCompleted(teamId);
                clearOnboardingTourActive();
                resetOnboardingTourAutoStartGuard(teamId);
                startOnboardingTour({ teamId, teamSlug, forced: true });
            }}
        >
            <Sparkles className="size-4" />
            Take a tour
        </Button>
    );
}
