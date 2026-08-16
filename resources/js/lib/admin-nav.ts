import {
    BarChart2,
    Briefcase,
    CreditCard,
    FileText,
    FolderTree,
    History,
    Layers,
    MessageSquare,
    Mic,
    QrCode,
    Radio,
    Shield,
    UserCog,
    Users,
} from 'lucide-react';
import type { NavGroup, NavItem } from '@/types';

export const adminOpsNavItems: NavItem[] = [
    { title: 'Subscribers', href: '/admin/subscribers', icon: Users },
    {
        title: 'Push test',
        href: '/admin/notifications-test',
        icon: MessageSquare,
    },
    { title: 'Plans', href: '/admin/plans', icon: CreditCard },
    { title: 'Payments', href: '/admin/payment-submissions', icon: QrCode },
    {
        title: 'Beta feedback',
        href: '/admin/beta-feedback',
        icon: MessageSquare,
    },
    { title: 'Payment QR', href: '/admin/payment-qr', icon: QrCode },
    { title: 'Users', href: '/admin/platform-users', icon: Shield },
    { title: 'Activity logs', href: '/admin/activity-logs', icon: History },
    {
        title: 'Formula templates',
        href: '/admin/formula-templates',
        icon: UserCog,
    },
    {
        title: 'Savings guidance',
        href: '/admin/savings-plan-guidance',
        icon: UserCog,
    },
];

export const contentNavItems: NavItem[] = [
    { title: 'Posts', href: '/admin/content/posts', icon: FileText },
    { title: 'Series', href: '/admin/content/series', icon: Layers },
    { title: 'Side hustles', href: '/admin/content/side-hustles', icon: Briefcase },
    {
        title: 'Hustle categories',
        href: '/admin/content/side-hustle-categories',
        icon: FolderTree,
    },
    { title: 'Podcasts', href: '/admin/content/podcast-shows', icon: Mic },
    { title: 'Podcast episodes', href: '/admin/content/podcast-episodes', icon: Radio },
    { title: 'Stats', href: '/admin/content/stats', icon: BarChart2 },
];

export const adminNavGroups: NavGroup[] = [
    { label: 'Admin', items: adminOpsNavItems },
    { label: 'Content', items: contentNavItems },
];

/** @deprecated Prefer adminNavGroups for grouped sidebar rendering. */
export const adminNavItems: NavItem[] = [
    ...adminOpsNavItems,
    ...contentNavItems,
];
