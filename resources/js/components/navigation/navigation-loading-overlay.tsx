import { Spinner } from '@/components/ui/spinner';
import { useNavigationLoading } from '@/contexts/navigation-loading-context';
import { useIsMobile } from '@/hooks/use-mobile';
import { cn } from '@/lib/utils';

type NavigationLoadingOverlayProps = {
    reserveBottomNav?: boolean;
};

export function NavigationLoadingOverlay({
    reserveBottomNav = true,
}: NavigationLoadingOverlayProps) {
    const isMobile = useIsMobile();
    const { isNavigating } = useNavigationLoading();

    if (!isMobile || !isNavigating) {
        return null;
    }

    return (
        <div
            aria-live="polite"
            aria-busy="true"
            className={cn(
                'absolute inset-0 z-40 flex flex-col items-center justify-center gap-3',
                'bg-background/60 backdrop-blur-[2px]',
                'transition-opacity duration-150',
                reserveBottomNav && 'pb-mobile-nav',
            )}
        >
            <Spinner className="size-8 text-primary" />
            <p className="text-sm text-muted-foreground">Loading…</p>
        </div>
    );
}
