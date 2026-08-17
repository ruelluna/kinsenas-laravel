import { Link, usePage } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import type { SharedData } from '@/types';

export type ContentEntity = 'posts' | 'series' | 'podcasts' | 'side-hustles' | 'community';
export type ContentSection = 'list' | 'settings' | 'stats';

type Props = {
    entity: ContentEntity;
    section: ContentSection;
};

const entities: Array<{
    key: ContentEntity;
    label: string;
    href: string;
    testId: string;
    authorVisible: boolean;
}> = [
    {
        key: 'posts',
        label: 'Posts',
        href: '/admin/content/posts',
        testId: 'content-entity-tab-posts',
        authorVisible: true,
    },
    {
        key: 'series',
        label: 'Series',
        href: '/admin/content/series',
        testId: 'content-entity-tab-series',
        authorVisible: false,
    },
    {
        key: 'podcasts',
        label: 'Podcasts',
        href: '/admin/content/podcasts',
        testId: 'content-entity-tab-podcasts',
        authorVisible: false,
    },
    {
        key: 'side-hustles',
        label: 'Side hustles',
        href: '/admin/content/side-hustles',
        testId: 'content-entity-tab-side-hustles',
        authorVisible: true,
    },
    {
        key: 'community',
        label: 'Community',
        href: '/admin/content/community',
        testId: 'content-entity-tab-community',
        authorVisible: false,
    },
];

const sections: Record<ContentEntity, Record<ContentSection, string>> = {
    posts: {
        list: '/admin/content/posts',
        settings: '/admin/content/posts/settings',
        stats: '/admin/content/posts/stats',
    },
    series: {
        list: '/admin/content/series',
        settings: '/admin/content/series/settings',
        stats: '/admin/content/series/stats',
    },
    podcasts: {
        list: '/admin/content/podcasts',
        settings: '/admin/content/podcasts/settings',
        stats: '/admin/content/podcasts/stats',
    },
    'side-hustles': {
        list: '/admin/content/side-hustles',
        settings: '/admin/content/side-hustles/settings',
        stats: '/admin/content/side-hustles/stats',
    },
    community: {
        list: '/admin/content/community',
        settings: '/admin/content/community/settings',
        stats: '/admin/content/community/stats',
    },
};

const sectionLabels: Record<ContentSection, string> = {
    list: 'List',
    settings: 'Settings',
    stats: 'Stats',
};

export default function ContentEntityTabs({ entity, section }: Props) {
    const canManageAllContent = Boolean(
        usePage<SharedData>().props.auth.user?.canManageAllContent,
    );

    const visibleEntities = canManageAllContent
        ? entities
        : entities.filter((item) => item.authorVisible);

    const showSections = canManageAllContent;

    return (
        <div className="space-y-3 border-b pb-4">
            <nav className="flex flex-wrap gap-2" aria-label="Content entities">
                {visibleEntities.map((item) => (
                    <Link
                        key={item.key}
                        href={item.href}
                        data-test={item.testId}
                        className={cn(
                            'rounded-md px-3 py-1.5 text-sm font-medium transition-colors',
                            entity === item.key
                                ? 'bg-primary text-primary-foreground'
                                : 'text-muted-foreground hover:bg-muted hover:text-foreground',
                        )}
                        aria-current={entity === item.key ? 'page' : undefined}
                    >
                        {item.label}
                    </Link>
                ))}
            </nav>
            {showSections && (
                <nav className="flex flex-wrap gap-2" aria-label="Entity sections">
                    {(Object.keys(sectionLabels) as ContentSection[]).map((key) => (
                        <Link
                            key={key}
                            href={sections[entity][key]}
                            data-test={`content-section-tab-${entity}-${key}`}
                            className={cn(
                                'rounded-md px-3 py-1 text-sm transition-colors',
                                section === key
                                    ? 'bg-muted font-medium text-foreground'
                                    : 'text-muted-foreground hover:text-foreground',
                            )}
                            aria-current={section === key ? 'page' : undefined}
                        >
                            {sectionLabels[key]}
                        </Link>
                    ))}
                </nav>
            )}
        </div>
    );
}
