import { Head, Link, router } from '@inertiajs/react';
import { usePage } from '@inertiajs/react';
import LearnMarketingShell from '@/components/learn/learn-marketing-shell';
import LearnEmptyState from '@/components/learn/learn-empty-state';
import LearnNavTabs, {
    type LearnFilter,
    pillActive,
    pillBase,
    pillInactive,
} from '@/components/learn/learn-nav-tabs';
import { Badge } from '@/components/ui/badge';
import { formatMoney } from '@/lib/format-money';
import { dashboard } from '@/routes';
import { cn } from '@/lib/utils';
import type { SharedData } from '@/types';
import type {
    ContentPostSummary,
    ContentSeriesSummary,
    PaginatedPosts,
} from '@/types/content';
import type {
    PaginatedLibrary,
    PodcastShowSummary,
    SideHustleCategorySummary,
    SideHustleSummary,
} from '@/types/learn-library';

type Props = {
    hasFullAccess: boolean;
    filter: string;
    activeCategory: string | null;
    posts: PaginatedPosts<ContentPostSummary>;
    series: ContentSeriesSummary[];
    categories: SideHustleCategorySummary[];
    hustles: PaginatedLibrary<SideHustleSummary> | null;
    hustlePreviews: SideHustleSummary[];
    shows: PodcastShowSummary[];
    isAuthenticated: boolean;
};

export default function LearnIndex({
    hasFullAccess,
    filter,
    activeCategory,
    posts,
    series,
    categories,
    hustles,
    hustlePreviews,
    shows,
    isAuthenticated,
}: Props) {
    const page = usePage<SharedData>();
    const dashboardUrl = page.props.currentTeam
        ? dashboard(page.props.currentTeam.slug)
        : '/';

    const activeFilter = (filter || 'all') as LearnFilter;
    const showPosts = ['all', 'series', 'reminders', 'articles'].includes(activeFilter);
    const showSeries = ['all', 'series'].includes(activeFilter);
    const showHustleLibrary = activeFilter === 'side-hustles';
    const showPodcastLibrary = activeFilter === 'podcasts';

    const content = (
        <>
            <Head title="Learn" />
            <div className="space-y-8">
                <div>
                    <h1 className="text-3xl font-semibold tracking-tight">Learn</h1>
                    <p className="mt-2 text-muted-foreground">
                        {hasFullAccess
                            ? 'Articles, reminders, series, side hustles, and podcasts to help you plan every sweldo.'
                            : 'Free previews from Kinsenas — sign in to read the full library.'}
                    </p>
                </div>

                <LearnNavTabs filter={activeFilter} hasFullAccess={hasFullAccess} />

                {showHustleLibrary && categories.length > 0 && (
                    <div
                        role="group"
                        aria-label="Category"
                        className="inline-flex flex-wrap gap-1 rounded-lg border bg-muted/40 p-1"
                    >
                        <button
                            type="button"
                            className={cn(
                                pillBase,
                                activeCategory ? pillInactive : pillActive,
                            )}
                            onClick={() =>
                                router.get('/learn', { filter: 'side-hustles' }, { preserveState: true })
                            }
                        >
                            All categories
                        </button>
                        {categories.map((category) => (
                            <button
                                key={category.id}
                                type="button"
                                className={cn(
                                    pillBase,
                                    activeCategory === category.slug ? pillActive : pillInactive,
                                )}
                                onClick={() =>
                                    router.get(
                                        '/learn',
                                        { filter: 'side-hustles', category: category.slug },
                                        { preserveState: true },
                                    )
                                }
                            >
                                {category.name}
                            </button>
                        ))}
                    </div>
                )}

                {showSeries && (series.length > 0 || activeFilter === 'series') && (
                    <section className="space-y-3">
                        <h2 className="text-lg font-medium">Series</h2>
                        {series.length === 0 ? (
                            <LearnEmptyState
                                title="No series yet"
                                description="Multi-part guides will appear here when published."
                                testId="learn-empty-series"
                            />
                        ) : (
                            <div className="grid gap-3 sm:grid-cols-2">
                                {series.map((item) => (
                                    <Link
                                        key={item.id}
                                        href={`/learn/series/${item.slug}`}
                                        className="rounded-lg border p-4 transition hover:border-primary/40"
                                    >
                                        <p className="font-medium">{item.title}</p>
                                        {item.description && (
                                            <p className="mt-1 text-sm text-muted-foreground line-clamp-2">
                                                {item.description}
                                            </p>
                                        )}
                                    </Link>
                                ))}
                            </div>
                        )}
                    </section>
                )}

                {showPosts && (
                    <section className="space-y-3">
                        <h2 className="text-lg font-medium">
                            {hasFullAccess ? 'Latest' : 'Free previews'}
                        </h2>
                        {posts.data.length === 0 ? (
                            <LearnEmptyState
                                title="No posts yet"
                                description={
                                    hasFullAccess
                                        ? 'Articles and reminders will show up here as they are published.'
                                        : 'Check back soon for free previews from Kinsenas.'
                                }
                                testId="learn-empty-posts"
                            />
                        ) : (
                            <div className="grid gap-4">
                                {posts.data.map((post) => (
                                    <Link
                                        key={post.id}
                                        href={`/learn/posts/${post.slug}`}
                                        className="block rounded-lg border p-4 transition hover:border-primary/40"
                                    >
                                        <div className="flex flex-wrap items-center gap-2">
                                            <Badge variant="secondary">{post.contentTypeLabel}</Badge>
                                            {post.series && (
                                                <span className="text-xs text-muted-foreground">
                                                    {post.series.title}
                                                    {post.episodeNumber
                                                        ? ` · Ep ${post.episodeNumber}`
                                                        : ''}
                                                </span>
                                            )}
                                        </div>
                                        <p className="mt-2 font-medium">{post.title}</p>
                                        {post.excerpt && (
                                            <p className="mt-1 text-sm text-muted-foreground line-clamp-2">
                                                {post.excerpt}
                                            </p>
                                        )}
                                    </Link>
                                ))}
                            </div>
                        )}
                    </section>
                )}

                {activeFilter === 'all' && hustlePreviews.length > 0 && (
                    <section className="space-y-3">
                        <div className="flex items-center justify-between gap-4">
                            <h2 className="text-lg font-medium">Side hustles</h2>
                            <button
                                type="button"
                                className="text-sm text-muted-foreground hover:text-foreground"
                                onClick={() =>
                                    router.get('/learn', { filter: 'side-hustles' }, { preserveState: true })
                                }
                            >
                                See all →
                            </button>
                        </div>
                        <div className="grid gap-4 sm:grid-cols-2">
                            {hustlePreviews.map((hustle) => (
                                <Link
                                    key={hustle.id}
                                    href={`/learn/side-hustles/${hustle.slug}`}
                                    className="rounded-lg border p-4 transition hover:border-primary/40"
                                >
                                    <div className="flex flex-wrap gap-2">
                                        {hustle.category && (
                                            <Badge variant="secondary">{hustle.category.name}</Badge>
                                        )}
                                        <Badge variant="outline">{hustle.difficultyLabel}</Badge>
                                    </div>
                                    <h3 className="mt-3 font-medium">{hustle.title}</h3>
                                    {hustle.excerpt && (
                                        <p className="mt-2 text-sm text-muted-foreground line-clamp-2">
                                            {hustle.excerpt}
                                        </p>
                                    )}
                                </Link>
                            ))}
                        </div>
                    </section>
                )}

                {showHustleLibrary && hustles && (
                    <section className="space-y-3">
                        <h2 className="text-lg font-medium">Side hustle library</h2>
                        {hustles.data.length === 0 ? (
                            <LearnEmptyState
                                title="No side hustles yet"
                                description={
                                    activeCategory
                                        ? 'Nothing in this category yet. Try another filter or check back later.'
                                        : 'Income ideas and starter guides will appear here when published.'
                                }
                                testId="learn-empty-side-hustles"
                            />
                        ) : (
                            <div className="grid gap-4 sm:grid-cols-2">
                                {hustles.data.map((hustle) => (
                                    <Link
                                        key={hustle.id}
                                        href={`/learn/side-hustles/${hustle.slug}`}
                                        className="rounded-lg border p-4 transition hover:border-primary/40"
                                    >
                                        <div className="flex flex-wrap gap-2">
                                            {hustle.category && (
                                                <Badge variant="secondary">{hustle.category.name}</Badge>
                                            )}
                                            <Badge variant="outline">{hustle.difficultyLabel}</Badge>
                                            <Badge variant="outline">{hustle.capitalTierLabel}</Badge>
                                        </div>
                                        <h3 className="mt-3 text-lg font-medium">{hustle.title}</h3>
                                        {hustle.excerpt && (
                                            <p className="mt-2 text-sm text-muted-foreground">
                                                {hustle.excerpt}
                                            </p>
                                        )}
                                        {(hustle.startupCapitalMin || hustle.startupCapitalMax) && (
                                            <p className="mt-3 text-sm">
                                                Startup: {formatMoney(hustle.startupCapitalMin)} –{' '}
                                                {formatMoney(hustle.startupCapitalMax)}
                                            </p>
                                        )}
                                    </Link>
                                ))}
                            </div>
                        )}
                        {!hasFullAccess && (
                            <p className="text-sm text-muted-foreground">
                                {isAuthenticated
                                    ? 'Subscribe to read full hustle guides.'
                                    : 'Sign in and subscribe to read full hustle guides.'}
                            </p>
                        )}
                    </section>
                )}

                {(activeFilter === 'all' || showPodcastLibrary) &&
                    (shows.length > 0 || showPodcastLibrary) && (
                    <section className="space-y-3">
                        <div className="flex items-center justify-between gap-4">
                            <h2 className="text-lg font-medium">Podcasts</h2>
                            {activeFilter === 'all' && shows.length > 0 && (
                                <button
                                    type="button"
                                    className="text-sm text-muted-foreground hover:text-foreground"
                                    onClick={() =>
                                        router.get('/learn', { filter: 'podcasts' }, { preserveState: true })
                                    }
                                >
                                    See all →
                                </button>
                            )}
                        </div>
                        {shows.length === 0 ? (
                            <LearnEmptyState
                                title="No podcasts yet"
                                description="Shows and episodes will appear here when published."
                                testId="learn-empty-podcasts"
                            />
                        ) : (
                            <div className="grid gap-4 sm:grid-cols-2">
                                {shows.map((show) => (
                                    <Link
                                        key={show.id}
                                        href={`/learn/podcasts/${show.slug}`}
                                        className="rounded-lg border p-4 transition hover:border-primary/40"
                                    >
                                        <h3 className="text-lg font-medium">{show.title}</h3>
                                        {show.description && (
                                            <p className="mt-2 text-sm text-muted-foreground line-clamp-2">
                                                {show.description}
                                            </p>
                                        )}
                                    </Link>
                                ))}
                            </div>
                        )}
                        {showPodcastLibrary && !hasFullAccess && shows.length > 0 && (
                            <p className="text-sm text-muted-foreground">
                                Some episodes may require a subscription for full show notes.
                            </p>
                        )}
                    </section>
                )}
            </div>
        </>
    );

    if (isAuthenticated) {
        return content;
    }

    return (
        <LearnMarketingShell
            isAuthenticated={isAuthenticated}
            dashboardUrl={dashboardUrl}
        >
            {content}
        </LearnMarketingShell>
    );
}
