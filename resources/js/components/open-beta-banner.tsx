import { Link, usePage } from '@inertiajs/react';
import { PublicBetaAlert } from '@/components/public-beta-alert';
import { pageContentPaddingX } from '@/components/page-content';
import { useDismissibleBanner } from '@/hooks/use-dismissible-banner';
import { OPEN_BETA_BANNER_DISMISS_KEY } from '@/lib/dismissible-banner';
import { cn } from '@/lib/utils';
import type { SharedData } from '@/types';

export function OpenBetaBanner() {
    const { openBeta } = usePage<SharedData>().props;
    const { dismissed, dismiss } = useDismissibleBanner(
        OPEN_BETA_BANNER_DISMISS_KEY,
    );

    if (!openBeta.isActive || !openBeta.isApproved || dismissed) {
        return null;
    }

    return (
        <div
            className={cn(
                'border-b border-sidebar-border/50 bg-primary/5',
                pageContentPaddingX,
                'py-3',
            )}
        >
            <PublicBetaAlert onDismiss={dismiss}>
                <p>
                    Use the core savings planner with your real account.{' '}
                    <Link
                        href="/settings/feedback"
                        className="font-medium underline underline-offset-4"
                    >
                        Send feedback
                    </Link>{' '}
                    anytime.
                </p>
            </PublicBetaAlert>
        </div>
    );
}
