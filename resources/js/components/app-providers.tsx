import type { ReactNode } from 'react';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { NavigationLoadingProvider } from '@/contexts/navigation-loading-context';

type AppProvidersProps = {
    children: ReactNode;
};

export default function AppProviders({ children }: AppProvidersProps) {
    return (
        <NavigationLoadingProvider>
            <TooltipProvider delayDuration={0}>
                {children}
                <Toaster />
            </TooltipProvider>
        </NavigationLoadingProvider>
    );
}
