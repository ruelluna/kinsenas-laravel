import {
    ClipboardList,
    CreditCard,
    KeyRound,
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
        title: 'Beta applications',
        href: '/admin/beta-applications',
        icon: ClipboardList,
    },
    {
        title: 'Beta access codes',
        href: '/admin/beta-access-codes',
        icon: KeyRound,
    },
    {
        title: 'Beta feedback',
        href: '/admin/beta-feedback',
        icon: MessageSquare,
    },
    { title: 'Payment QR', href: '/admin/payment-qr', icon: QrCode },
    { title: 'Users', href: '/admin/platform-users', icon: Shield },
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
