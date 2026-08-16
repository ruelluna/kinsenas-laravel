import {
    BookOpen,
    CreditCard,
    Landmark,
    LayoutGrid,
    MessageSquare,
    PiggyBank,
    ArrowRightLeft,
    ShoppingBag,
    Users,
    Wallet,
} from 'lucide-react';
import { dashboard } from '@/routes';
import { toUrl } from '@/lib/utils';
import type { NavItem, SharedData } from '@/types';

export type MemberNavConfig = {
    homeUrl: string;
    hasAccess: boolean;
    mainNavItems: NavItem[];
    bottomTabs: NavItem[];
    moreItems: NavItem[];
    billingNavItems: NavItem[];
};

export function buildMemberNav(page: SharedData): MemberNavConfig {
    const teamSlug = page.currentTeam?.slug;
    const subscription = page.subscription;
    const openBeta = page.openBeta;
    const hasAccess = subscription?.hasAccess ?? true;
    const dashboardUrl = teamSlug ? toUrl(dashboard(teamSlug)) : '/';
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

    const allItems: NavItem[] = hasAccess
        ? [
              {
                  title: 'Dashboard',
                  href: dashboardUrl,
                  icon: LayoutGrid,
              },
              {
                  title: 'Banks',
                  href: `${savingsBase}/banks`,
                  icon: Landmark,
                  tourId: 'nav-banks',
              },
              {
                  title: 'Savings Plan',
                  href: `${savingsBase}/plan`,
                  icon: PiggyBank,
                  tourId: 'nav-plan',
              },
              {
                  title: 'Income',
                  href: `${savingsBase}/income`,
                  icon: Wallet,
                  tourId: 'nav-income',
              },
              {
                  title: 'Transfers',
                  href: `${savingsBase}/transfers`,
                  icon: ArrowRightLeft,
                  tourId: 'nav-transfers',
              },
              {
                  title: 'Spending',
                  href: `${savingsBase}/spending`,
                  icon: ShoppingBag,
                  tourId: 'nav-spending',
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
              ...(openBeta.isActive && openBeta.isParticipant
                  ? [
                        {
                            title: 'Feedback',
                            href: '/settings/feedback',
                            icon: MessageSquare,
                        } satisfies NavItem,
                    ]
                  : []),
          ]
        : billingNavItems;

    const bottomTabTitles = new Set(['Dashboard', 'Income', 'Spending']);
    const bottomTabs = allItems.filter((item) =>
        bottomTabTitles.has(item.title),
    );
    const moreItems = allItems.filter(
        (item) => !bottomTabTitles.has(item.title),
    );

    return {
        homeUrl,
        hasAccess,
        mainNavItems: allItems,
        bottomTabs,
        moreItems,
        billingNavItems,
    };
}
