import { Link, usePage } from '@inertiajs/react';
import { Menu } from 'lucide-react';
import { useEffect, useMemo, type ReactNode } from 'react';
import { registerMobileMoreSheetController } from '@/lib/mobile-more-sheet-bridge';
import { MobileMoreSheet } from '@/components/mobile/mobile-more-sheet';
import { Spinner } from '@/components/ui/spinner';
import { useNavigationLoading } from '@/contexts/navigation-loading-context';
import { useMobileNav } from '@/contexts/mobile-nav-context';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { useIsMobile } from '@/hooks/use-mobile';
import { buildMemberNav } from '@/lib/member-nav';
import { cn, toUrl } from '@/lib/utils';
import type { NavItem, SharedData } from '@/types';

export function MobileBottomNav() {
    const isMobile = useIsMobile();
    const page = usePage<SharedData>();
    const { action, moreSheetOpen, setMoreSheetOpen } = useMobileNav();
    const { isCurrentOrParentUrl } = useCurrentUrl();
    const { isNavigating, pendingUrl } = useNavigationLoading();

    useEffect(() => {
        return registerMobileMoreSheetController({
            open: () => setMoreSheetOpen(true),
            close: () => setMoreSheetOpen(false),
        });
    }, [setMoreSheetOpen]);
    const { bottomTabs, moreItems, hasAccess, billingNavItems } =
        buildMemberNav(page.props);

    const moreIsActive = useMemo(
        () => moreItems.some((item) => isCurrentOrParentUrl(item.href)),
        [isCurrentOrParentUrl, moreItems],
    );

    const moreIsPending = useMemo(
        () =>
            Boolean(
                pendingUrl &&
                    moreItems.some((item) =>
                        isPendingDestination(item.href, pendingUrl),
                    ),
            ),
        [moreItems, pendingUrl],
    );

    const isItemPending = (item: NavItem) =>
        Boolean(
            pendingUrl && isPendingDestination(item.href, pendingUrl),
        );

    const showCenterAction = Boolean(
        action?.icon && !action.disabled,
    );
    const ActionIcon = action?.icon;

    if (!isMobile) {
        return null;
    }

    if (!hasAccess) {
        return (
            <>
                <MobileNavBar>
                    <div className="flex h-16 items-center justify-around px-4">
                        <MobileNavLink
                            item={billingNavItems[0]}
                            active={isCurrentOrParentUrl('/settings/billing')}
                            pending={isItemPending(billingNavItems[0])}
                            dimmed={isNavigating && !isItemPending(billingNavItems[0])}
                        />
                        <MobileMoreButton
                            active={moreIsActive}
                            pending={moreIsPending}
                            dimmed={isNavigating && !moreIsPending}
                            onClick={() => setMoreSheetOpen(true)}
                        />
                    </div>
                </MobileNavBar>
                <MobileMoreSheet
                    open={moreSheetOpen}
                    onOpenChange={setMoreSheetOpen}
                />
            </>
        );
    }

    return (
        <>
            <MobileNavBar>
                {showCenterAction ? (
                    <div className="grid h-16 grid-cols-5 items-end px-2">
                        <MobileNavLink
                            item={bottomTabs[0]}
                            active={isCurrentOrParentUrl(bottomTabs[0].href)}
                            pending={isItemPending(bottomTabs[0])}
                            dimmed={isNavigating && !isItemPending(bottomTabs[0])}
                            compact
                        />
                        <MobileNavLink
                            item={bottomTabs[1]}
                            active={isCurrentOrParentUrl(bottomTabs[1].href)}
                            pending={isItemPending(bottomTabs[1])}
                            dimmed={isNavigating && !isItemPending(bottomTabs[1])}
                            compact
                        />
                        <div className="flex justify-center pb-2">
                            {ActionIcon && action ? (
                                <button
                                    type="button"
                                    aria-label={action.ariaLabel}
                                    onClick={action.onClick}
                                    className="flex size-14 -translate-y-3 flex-col items-center justify-center rounded-full bg-primary text-primary-foreground shadow-lg"
                                >
                                    <ActionIcon className="size-6" />
                                    <span className="sr-only">
                                        {action.label}
                                    </span>
                                </button>
                            ) : null}
                        </div>
                        <MobileNavLink
                            item={bottomTabs[2]}
                            active={isCurrentOrParentUrl(bottomTabs[2].href)}
                            pending={isItemPending(bottomTabs[2])}
                            dimmed={isNavigating && !isItemPending(bottomTabs[2])}
                            compact
                        />
                        <MobileMoreButton
                            active={moreIsActive}
                            pending={moreIsPending}
                            dimmed={isNavigating && !moreIsPending}
                            onClick={() => setMoreSheetOpen(true)}
                            compact
                        />
                    </div>
                ) : (
                    <div className="grid h-16 grid-cols-4 items-end px-2">
                        {bottomTabs.map((item) => (
                            <MobileNavLink
                                key={item.title}
                                item={item}
                                active={isCurrentOrParentUrl(item.href)}
                                pending={isItemPending(item)}
                                dimmed={isNavigating && !isItemPending(item)}
                                compact
                            />
                        ))}
                        <MobileMoreButton
                            active={moreIsActive}
                            pending={moreIsPending}
                            dimmed={isNavigating && !moreIsPending}
                            onClick={() => setMoreSheetOpen(true)}
                            compact
                        />
                    </div>
                )}
            </MobileNavBar>
            <MobileMoreSheet
                open={moreSheetOpen}
                onOpenChange={setMoreSheetOpen}
            />
        </>
    );
}

function MobileNavBar({ children }: { children: ReactNode }) {
    return (
        <nav
            aria-label="Mobile navigation"
            className="fixed inset-x-0 bottom-0 z-50 border-t border-border bg-background/95 pb-[env(safe-area-inset-bottom,0px)] backdrop-blur-md md:hidden"
        >
            <div className="mx-auto max-w-lg">{children}</div>
        </nav>
    );
}

function MobileMoreButton({
    active,
    pending = false,
    dimmed = false,
    onClick,
    compact = false,
}: {
    active: boolean;
    pending?: boolean;
    dimmed?: boolean;
    onClick: () => void;
    compact?: boolean;
}) {
    return (
        <button
            type="button"
            aria-label="Open more navigation"
            onClick={onClick}
            className={cn(
                'flex flex-col items-center gap-0.5 py-2 text-[11px] transition-opacity',
                compact ? 'px-1' : 'px-2',
                dimmed && 'opacity-40',
                pending || active
                    ? 'font-medium text-primary'
                    : 'text-muted-foreground',
            )}
        >
            {pending ? (
                <Spinner className="size-5 shrink-0" />
            ) : (
                <Menu className="size-5 shrink-0" />
            )}
            <span>More</span>
        </button>
    );
}

function isPendingDestination(
    href: NavItem['href'],
    pendingUrl: string,
): boolean {
    const path = toUrl(href);

    return pendingUrl === path || pendingUrl.startsWith(`${path}/`);
}

function MobileNavLink({
    item,
    active,
    pending = false,
    dimmed = false,
    compact = false,
}: {
    item: NavItem;
    active: boolean;
    pending?: boolean;
    dimmed?: boolean;
    compact?: boolean;
}) {
    const Icon = item.icon;
    const shortLabel =
        item.title === 'Dashboard'
            ? 'Home'
            : item.title.replace('Savings ', '');

    return (
        <Link
            href={item.href}
            prefetch
            data-tour={item.tourId}
            className={cn(
                'flex flex-col items-center gap-0.5 py-2 text-[11px] transition-opacity',
                compact ? 'px-1' : 'px-2',
                dimmed && 'opacity-40',
                pending || active
                    ? 'font-medium text-primary'
                    : 'text-muted-foreground',
            )}
        >
            {pending && Icon ? (
                <Spinner className="size-5 shrink-0" />
            ) : (
                Icon && <Icon className="size-5 shrink-0" />
            )}
            <span className="max-w-[4.5rem] truncate">{shortLabel}</span>
        </Link>
    );
}
