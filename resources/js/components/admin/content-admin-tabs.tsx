import { Link, usePage } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import type { SharedData } from '@/types';

type Tab =
    | 'posts'
    | 'post-categories'
    | 'series'
    | 'side-hustles'
    | 'hustle-categories'
    | 'podcasts'
    | 'podcast-episodes'
    | 'community-categories'
    | 'community-posts'
    | 'community-moderation'
    | 'community-reports'
    | 'stats';

type Props = {
    active: Tab;
};

const tabs: Array<{ key: Tab; label: string; href: string; testId: string; platformOnly?: boolean }> = [
    {
        key: 'posts',
        label: 'Posts',
        href: '/admin/content/posts',
        testId: 'content-admin-tab-posts',
    },
    {
        key: 'post-categories',
        label: 'Post categories',
        href: '/admin/content/post-categories',
        testId: 'content-admin-tab-post-categories',
        platformOnly: true,
    },
    {
        key: 'series',
        label: 'Series',
        href: '/admin/content/series',
        testId: 'content-admin-tab-series',
        platformOnly: true,
    },
    {
        key: 'side-hustles',
        label: 'Side hustles',
        href: '/admin/content/side-hustles',
        testId: 'content-admin-tab-side-hustles',
    },
    {
        key: 'hustle-categories',
        label: 'Categories',
        href: '/admin/content/side-hustle-categories',
        testId: 'content-admin-tab-hustle-categories',
        platformOnly: true,
    },
    {
        key: 'podcasts',
        label: 'Podcasts',
        href: '/admin/content/podcast-shows',
        testId: 'content-admin-tab-podcasts',
        platformOnly: true,
    },
    {
        key: 'podcast-episodes',
        label: 'Episodes',
        href: '/admin/content/podcast-episodes',
        testId: 'content-admin-tab-podcast-episodes',
        platformOnly: true,
    },
    {
        key: 'community-categories',
        label: 'Community categories',
        href: '/admin/content/community-categories',
        testId: 'content-admin-tab-community-categories',
        platformOnly: true,
    },
    {
        key: 'community-posts',
        label: 'Community posts',
        href: '/admin/content/community-posts',
        testId: 'content-admin-tab-community-posts',
        platformOnly: true,
    },
    {
        key: 'community-moderation',
        label: 'Moderation queue',
        href: '/admin/content/community-posts/pending',
        testId: 'content-admin-tab-community-moderation',
        platformOnly: true,
    },
    {
        key: 'community-reports',
        label: 'Reports',
        href: '/admin/content/community-reports',
        testId: 'content-admin-tab-community-reports',
        platformOnly: true,
    },
    {
        key: 'stats',
        label: 'Stats',
        href: '/admin/content/stats',
        testId: 'content-admin-tab-stats',
        platformOnly: true,
    },
];

export default function ContentAdminTabs({ active }: Props) {
    const canManageAllContent = Boolean(
        usePage<SharedData>().props.auth.user?.canManageAllContent,
    );
    const visibleTabs = canManageAllContent
        ? tabs
        : tabs.filter((tab) => !tab.platformOnly);

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
