import { Link, usePage } from '@inertiajs/react';
import { BookOpen, CreditCard, Landmark, LayoutGrid, MessageSquare, PiggyBank, ArrowRightLeft, ShoppingBag, Users, Wallet } from 'lucide-react';
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
import { dashboard } from '@/routes';
import type { NavItem, SharedData } from '@/types';

export function AppSidebar() {
    const page = usePage<SharedData>();
    const teamSlug = page.props.currentTeam?.slug;
    const subscription = page.props.subscription;
    const openBeta = page.props.openBeta;
    const hasAccess = subscription?.hasAccess ?? true;
    const isPlatformAdmin = Boolean(page.props.auth.user?.isPlatformAdmin);
    const dashboardUrl = teamSlug ? dashboard(teamSlug) : '/';
    const savingsBase = teamSlug ? `/${teamSlug}/savings` : '/';
    const billingUrl = '/settings/billing';
    const homeUrl = hasAccess ? dashboardUrl : billingUrl;

    const billingNavItems: NavItem[] = [
        {
            title: 'Billing',
            href: billingUrl,
            icon: CreditCard,
        },
    ];

    const mainNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboardUrl,
            icon: LayoutGrid,
        },
        {
            title: 'Savings Plan',
            href: `${savingsBase}/plan`,
            icon: PiggyBank,
        },
        {
            title: 'Income',
            href: `${savingsBase}/income`,
            icon: Wallet,
        },
        {
            title: 'Transfers',
            href: `${savingsBase}/transfers`,
            icon: ArrowRightLeft,
        },
        {
            title: 'Spending',
            href: `${savingsBase}/spending`,
            icon: ShoppingBag,
        },
        {
            title: 'Banks',
            href: `${savingsBase}/banks`,
            icon: Landmark,
        },
        {
            title: 'Recipients',
            href: `${savingsBase}/recipients`,
            icon: Users,
        },
        {
            title: 'Reports',
            href: `${savingsBase}/reports`,
            icon: BookOpen,
        },
        ...(openBeta.isActive && openBeta.isApproved
            ? [
                  {
                      title: 'Feedback',
                      href: '/settings/feedback',
                      icon: MessageSquare,
                  } satisfies NavItem,
              ]
            : []),
    ];

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
