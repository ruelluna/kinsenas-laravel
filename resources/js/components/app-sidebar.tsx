import { Link, usePage } from '@inertiajs/react';
import { AdminSidebarNav } from '@/components/admin/admin-sidebar-nav';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { TeamSwitcher } from '@/components/team-switcher';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { buildMemberNav } from '@/lib/member-nav';
import type { SharedData } from '@/types';

export function AppSidebar() {
    const page = usePage<SharedData>();
    const hasAccess = page.props.subscription?.hasAccess ?? true;
    const isPlatformAdmin = Boolean(page.props.auth.user?.isPlatformAdmin);
    const { homeUrl, mainNavItems, billingNavItems } = buildMemberNav(
        page.props,
    );

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={homeUrl} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <TeamSwitcher />
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={hasAccess ? mainNavItems : billingNavItems} />
                {hasAccess && isPlatformAdmin && <AdminSidebarNav />}
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
