import {
    CreditCard,
    History,
    MessageSquare,
    QrCode,
    Shield,
    UserCog,
    Users,
} from 'lucide-react';
import type { NavItem } from '@/types';

export const adminNavItems: NavItem[] = [
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
