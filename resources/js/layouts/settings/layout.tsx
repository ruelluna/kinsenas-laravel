import { Link, usePage } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn, toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import { index as teams } from '@/routes/teams';
import type { NavItem, SharedData } from '@/types';

const baseSidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: edit(),
        icon: null,
    },
    {
        title: 'Security',
        href: editSecurity(),
        icon: null,
    },
    {
        title: 'Teams',
        href: teams(),
        icon: null,
    },
    {
        title: 'Billing',
        href: '/settings/billing',
        icon: null,
    },
];

export default function SettingsLayout({ children }: PropsWithChildren) {
    const { openBeta } = usePage<SharedData>().props;
    const { isCurrentOrParentUrl } = useCurrentUrl();

    const sidebarNavItems: NavItem[] = [
        ...baseSidebarNavItems,
        ...(openBeta.isActive && openBeta.isApproved
            ? [{ title: 'Feedback', href: '/settings/feedback', icon: null }]
            : []),
        {
            title: 'Appearance',
            href: editAppearance(),
            icon: null,
        },
    ];

    return (
        <div>
            <Heading
                title="Settings"
                description="Manage your profile and account settings"
            />

            <div className="flex flex-col lg:flex-row lg:space-x-12">
                <aside className="w-full lg:w-48 lg:max-w-none">
                    <nav
                        className="-mx-4 flex gap-1 overflow-x-auto px-4 pb-1 lg:mx-0 lg:flex-col lg:gap-0 lg:overflow-visible lg:px-0 lg:pb-0"
                        aria-label="Settings"
                    >
                        {sidebarNavItems.map((item, index) => (
                            <Button
                                key={`${toUrl(item.href)}-${index}`}
                                size="sm"
                                variant="ghost"
                                asChild
                                className={cn(
                                    'shrink-0 justify-center lg:w-full lg:justify-start',
                                    {
                                        'bg-muted': isCurrentOrParentUrl(
                                            item.href,
                                        ),
                                    },
                                )}
                            >
                                <Link href={item.href}>
                                    {item.icon && (
                                        <item.icon className="h-4 w-4" />
                                    )}
                                    {item.title}
                                </Link>
                            </Button>
                        ))}
                    </nav>
                </aside>

                <Separator className="my-4 lg:hidden" />

                <div className="min-w-0 flex-1 lg:max-w-2xl">
                    <section className="space-y-8 lg:max-w-xl lg:space-y-12">
                        {children}
                    </section>
                </div>
            </div>
        </div>
    );
}
