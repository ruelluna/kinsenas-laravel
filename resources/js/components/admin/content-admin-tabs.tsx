import { Link, usePage } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import type { SharedData } from '@/types';

type Tab = 'posts' | 'series' | 'stats';

type Props = {
    active: Tab;
};

const tabs: Array<{ key: Tab; label: string; href: string; testId: string }> = [
    {
        key: 'posts',
        label: 'Posts',
        href: '/admin/content/posts',
        testId: 'content-admin-tab-posts',
    },
    {
        key: 'series',
        label: 'Series',
        href: '/admin/content/series',
        testId: 'content-admin-tab-series',
    },
    {
        key: 'stats',
        label: 'Stats',
        href: '/admin/content/stats',
        testId: 'content-admin-tab-stats',
    },
];

export default function ContentAdminTabs({ active }: Props) {
    const canManageAllContent = Boolean(
        usePage<SharedData>().props.auth.user?.canManageAllContent,
    );
    const visibleTabs = canManageAllContent
        ? tabs
        : tabs.filter((tab) => tab.key === 'posts');

    return (
        <nav
            className="flex flex-wrap gap-2 border-b pb-4"
            aria-label="Content admin"
        >
            {visibleTabs.map((tab) => (
                <Link
                    key={tab.key}
                    href={tab.href}
                    data-test={tab.testId}
                    className={cn(
                        'rounded-md px-3 py-1.5 text-sm font-medium transition-colors',
                        active === tab.key
                            ? 'bg-primary text-primary-foreground'
                            : 'text-muted-foreground hover:bg-muted hover:text-foreground',
                    )}
                    aria-current={active === tab.key ? 'page' : undefined}
                >
                    {tab.label}
                </Link>
            ))}
        </nav>
    );
}
