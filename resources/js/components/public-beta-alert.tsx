import type { ReactNode } from 'react';
import { DismissButton } from '@/components/dismiss-button';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { BETA_FREE_MESSAGE } from '@/lib/beta-copy';
import { cn } from '@/lib/utils';

type PublicBetaAlertProps = {
    className?: string;
    children?: ReactNode;
    onDismiss?: () => void;
};

export function PublicBetaAlert({
    className,
    children,
    onDismiss,
}: PublicBetaAlertProps) {
    return (
        <Alert
            variant="brand"
            className={cn(onDismiss && 'relative pr-12', className)}
        >
            {onDismiss ? (
                <DismissButton
                    onDismiss={onDismiss}
                    label="Dismiss public beta announcement"
                    className="absolute top-2 right-2"
                />
            ) : null}
            <AlertTitle>Public beta — free access</AlertTitle>
            <AlertDescription className="space-y-2">
                <p>{BETA_FREE_MESSAGE}</p>
                {children}
            </AlertDescription>
        </Alert>
    );
}
