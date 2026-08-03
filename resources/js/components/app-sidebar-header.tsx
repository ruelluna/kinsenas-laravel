import { Link, usePage } from '@inertiajs/react';
import AppLogo from '@/components/app-logo';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { pageContentPaddingX } from '@/components/page-content';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { useIsMobile } from '@/hooks/use-mobile';
import { buildMemberNav } from '@/lib/member-nav';
import { cn } from '@/lib/utils';
import type { BreadcrumbItem as BreadcrumbItemType, SharedData } from '@/types';

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    const isMobile = useIsMobile();
    const { homeUrl } = buildMemberNav(usePage<SharedData>().props);

    return (
        <header
            className={cn(
                'pwa-standalone-header safe-area-x flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/50 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12',
                pageContentPaddingX,
            )}
        >
            <div className="flex min-w-0 flex-1 items-center gap-2">
                {isMobile ? (
                    <Link
                        href={homeUrl}
                        prefetch
                        className="shrink-0"
                        aria-label="Kinsenas home"
                    >
                        <AppLogo />
                    </Link>
                ) : (
                    <SidebarTrigger className="-ml-1" />
                )}
                <Breadcrumbs breadcrumbs={breadcrumbs} />
            </div>
        </header>
    );
}
