import { Link } from '@inertiajs/react';
import {
    ClipboardList,
    CreditCard,
    MessageSquare,
    QrCode,
    Shield,
    UserCog,
    Users,
} from 'lucide-react';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

const adminNavItems: NavItem[] = [
    { title: 'Subscribers', href: '/admin/subscribers', icon: Users },
    { title: 'Plans', href: '/admin/plans', icon: CreditCard },
    { title: 'Payments', href: '/admin/payment-submissions', icon: QrCode },
    { title: 'Beta applications', href: '/admin/beta-applications', icon: ClipboardList },
    { title: 'Beta feedback', href: '/admin/beta-feedback', icon: MessageSquare },
    { title: 'Payment QR', href: '/admin/payment-qr', icon: QrCode },
    { title: 'Users', href: '/admin/platform-users', icon: Shield },
    { title: 'Formula templates', href: '/admin/formula-templates', icon: UserCog },
    { title: 'Savings guidance', href: '/admin/savings-plan-guidance', icon: UserCog },
];

export function AdminSidebarNav() {
    return (
        <SidebarGroup className="group-data-[collapsible=icon]:hidden">
            <SidebarGroupLabel>Admin</SidebarGroupLabel>
            <SidebarMenu>
                {adminNavItems.map((item) => (
                    <SidebarMenuItem key={item.title}>
                        <SidebarMenuButton asChild>
                            <Link href={item.href} prefetch>
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
