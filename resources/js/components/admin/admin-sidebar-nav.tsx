import { Link } from '@inertiajs/react';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCloseMobileSidebar } from '@/hooks/use-close-mobile-sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { adminNavGroups } from '@/lib/admin-nav';
import type { NavItem } from '@/types';

function isNavItemActive(
    item: NavItem,
    isCurrentOrParentUrl: ReturnType<typeof useCurrentUrl>['isCurrentOrParentUrl'],
): boolean {
    return isCurrentOrParentUrl(item.href);
}

export function AdminSidebarNav() {
    const closeMobileSidebar = useCloseMobileSidebar();
    const { isCurrentOrParentUrl } = useCurrentUrl();

    return (
        <>
            {adminNavGroups.map((group) => (
                <SidebarGroup
                    key={group.label}
                    className="group-data-[collapsible=icon]:hidden"
                >
                    <SidebarGroupLabel>{group.label}</SidebarGroupLabel>
                    <SidebarMenu>
                        {group.items.map((item) => (
                            <SidebarMenuItem key={item.title}>
                                <SidebarMenuButton
                                    asChild
                                    isActive={isNavItemActive(
                                        item,
                                        isCurrentOrParentUrl,
                                    )}
                                    tooltip={{ children: item.title }}
                                >
                                    <Link
                                        href={item.href}
                                        prefetch
                                        onClick={closeMobileSidebar}
                                    >
                                        {item.icon && <item.icon />}
                                        <span>{item.title}</span>
                                    </Link>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        ))}
                    </SidebarMenu>
                </SidebarGroup>
            ))}
        </>
    );
}
