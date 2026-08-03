import { Link, usePage } from '@inertiajs/react';
import { Menu } from 'lucide-react';
import { useMemo, useState, type ReactNode } from 'react';
import { MobileMoreSheet } from '@/components/mobile/mobile-more-sheet';
import { useMobileNav } from '@/contexts/mobile-nav-context';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { useIsMobile } from '@/hooks/use-mobile';
import { buildMemberNav } from '@/lib/member-nav';
import { cn } from '@/lib/utils';
import type { NavItem, SharedData } from '@/types';

export function MobileBottomNav() {
    const isMobile = useIsMobile();
    const page = usePage<SharedData>();
    const { action } = useMobileNav();
    const { isCurrentOrParentUrl } = useCurrentUrl();
    const [moreOpen, setMoreOpen] = useState(false);
    const { bottomTabs, moreItems, hasAccess, billingNavItems } =
        buildMemberNav(page.props);

    const moreIsActive = useMemo(
        () => moreItems.some((item) => isCurrentOrParentUrl(item.href)),
        [isCurrentOrParentUrl, moreItems],
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
                        />
                        <MobileMoreButton
                            active={moreIsActive}
                            onClick={() => setMoreOpen(true)}
                        />
                    </div>
                </MobileNavBar>
                <MobileMoreSheet open={moreOpen} onOpenChange={setMoreOpen} />
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
                            compact
                        />
                        <MobileNavLink
                            item={bottomTabs[1]}
                            active={isCurrentOrParentUrl(bottomTabs[1].href)}
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
                            compact
                        />
                        <MobileMoreButton
                            active={moreIsActive}
                            onClick={() => setMoreOpen(true)}
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
                                compact
                            />
                        ))}
                        <MobileMoreButton
                            active={moreIsActive}
                            onClick={() => setMoreOpen(true)}
                            compact
                        />
                    </div>
                )}
            </MobileNavBar>
            <MobileMoreSheet open={moreOpen} onOpenChange={setMoreOpen} />
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
    onClick,
    compact = false,
}: {
    active: boolean;
    onClick: () => void;
    compact?: boolean;
}) {
    return (
        <button
            type="button"
            aria-label="Open more navigation"
            onClick={onClick}
            className={cn(
                'flex flex-col items-center gap-0.5 py-2 text-[11px]',
                compact ? 'px-1' : 'px-2',
                active
                    ? 'font-medium text-primary'
                    : 'text-muted-foreground',
            )}
        >
            <Menu className="size-5 shrink-0" />
            <span>More</span>
        </button>
    );
}

function MobileNavLink({
    item,
    active,
    compact = false,
}: {
    item: NavItem;
    active: boolean;
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
            className={cn(
                'flex flex-col items-center gap-0.5 py-2 text-[11px]',
                compact ? 'px-1' : 'px-2',
                active
                    ? 'font-medium text-primary'
                    : 'text-muted-foreground',
            )}
        >
            {Icon && <Icon className="size-5 shrink-0" />}
            <span className="max-w-[4.5rem] truncate">{shortLabel}</span>
        </Link>
    );
}
