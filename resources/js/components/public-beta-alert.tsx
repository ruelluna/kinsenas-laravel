import type { ReactNode } from 'react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { BETA_FREE_MESSAGE } from '@/lib/beta-copy';
import { cn } from '@/lib/utils';

type PublicBetaAlertProps = {
    className?: string;
    children?: ReactNode;
};

export function PublicBetaAlert({ className, children }: PublicBetaAlertProps) {
    return (
        <Alert variant="brand" className={cn(className)}>
            <AlertTitle>Public beta — free access</AlertTitle>
            <AlertDescription className="space-y-2">
                <p>{BETA_FREE_MESSAGE}</p>
                {children}
            </AlertDescription>
        </Alert>
    );
}
