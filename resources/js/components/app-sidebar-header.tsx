import { Breadcrumbs } from '@/components/breadcrumbs';
import { MobileHeaderBar } from '@/components/mobile/mobile-header-bar';
import { NotificationBell } from '@/components/notifications/notification-bell';
import { pageContentPaddingX } from '@/components/page-content';
import { TeamSwitcher } from '@/components/team-switcher';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { cn } from '@/lib/utils';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    return (
        <header
            className={cn(
                'pwa-standalone-header sticky top-0 z-40 flex h-14 shrink-0 items-center gap-3 border-b border-sidebar-border/50 bg-background/95 backdrop-blur-md supports-[backdrop-filter]:bg-background/80',
                'md:h-16',
                pageContentPaddingX,
            )}
        >
            <div className="flex min-w-0 flex-1 items-center gap-2 md:hidden">
                <MobileHeaderBar breadcrumbs={breadcrumbs} />
            </div>

            <div className="hidden min-w-0 flex-1 items-center gap-2 md:flex">
                <SidebarTrigger className="-ml-1" />
                <Breadcrumbs breadcrumbs={breadcrumbs} />
            </div>

            <div className="flex shrink-0 items-center gap-2">
                <NotificationBell />
                <div className="md:hidden">
                    <TeamSwitcher inHeader compact />
                </div>
            </div>
        </header>
    );
}
