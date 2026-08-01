import { Link, usePage } from '@inertiajs/react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { pageContentPaddingX } from '@/components/page-content';
import { BETA_FREE_MESSAGE } from '@/lib/beta-copy';
import { cn } from '@/lib/utils';
import type { SharedData } from '@/types';

export function OpenBetaBanner() {
    const { openBeta } = usePage<SharedData>().props;

    if (!openBeta.isActive || !openBeta.isApproved) {
        return null;
    }

    return (
        <div className={cn('border-b border-sidebar-border/50 bg-primary/5', pageContentPaddingX, 'py-3')}>
            <Alert variant="info" className="border-primary/20 bg-background/80">
                <AlertTitle>Public beta — free access</AlertTitle>
                <AlertDescription className="space-y-2">
                    <p>{BETA_FREE_MESSAGE}</p>
                    <p>
                        Use the core savings planner with your real account.{' '}
                        <Link href="/settings/feedback" className="font-medium underline underline-offset-4">
                            Send feedback
                        </Link>{' '}
                        anytime.
                    </p>
                </AlertDescription>
            </Alert>
        </div>
    );
}
