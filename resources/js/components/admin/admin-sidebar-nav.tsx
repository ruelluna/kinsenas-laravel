import { Link } from '@inertiajs/react';
import { useCloseMobileSidebar } from '@/hooks/use-close-mobile-sidebar';
import { adminNavItems } from '@/lib/admin-nav';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';

export function AdminSidebarNav() {
    const closeMobileSidebar = useCloseMobileSidebar();

    return (
        <SidebarGroup className="group-data-[collapsible=icon]:hidden">
            <SidebarGroupLabel>Admin</SidebarGroupLabel>
            <SidebarMenu>
                {adminNavItems.map((item) => (
                    <SidebarMenuItem key={item.title}>
                        <SidebarMenuButton asChild>
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
    );
}
