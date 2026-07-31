import { Link, usePage } from '@inertiajs/react';
import { BookOpen, FolderGit2, Landmark, LayoutGrid, PiggyBank, ShoppingBag, Users, Wallet } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
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
import type { NavItem } from '@/types';

export function AppSidebar() {
    const page = usePage();
    const teamSlug = page.props.currentTeam?.slug;
    const dashboardUrl = teamSlug ? dashboard(teamSlug) : '/';
    const savingsBase = teamSlug ? `/${teamSlug}/savings` : '/';

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
    ];

    const footerNavItems: NavItem[] = [
        {
            title: 'Repository',
            href: 'https://github.com/laravel/react-starter-kit',
            icon: FolderGit2,
        },
    ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboardUrl} prefetch>
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
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
