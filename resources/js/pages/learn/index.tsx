import { Head, Link, router } from '@inertiajs/react';
import { usePage } from '@inertiajs/react';
import LearnMarketingShell from '@/components/learn/learn-marketing-shell';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import type { SharedData } from '@/types';
import type {
    ContentPostSummary,
    ContentSeriesSummary,
    PaginatedPosts,
} from '@/types/content';

type Props = {
    hasFullAccess: boolean;
    filter: string;
    posts: PaginatedPosts<ContentPostSummary>;
    series: ContentSeriesSummary[];
    isAuthenticated: boolean;
};

const filters = [
    { value: 'all', label: 'All' },
    { value: 'series', label: 'Series' },
    { value: 'reminders', label: 'Reminders' },
    { value: 'articles', label: 'Articles' },
];

export default function LearnIndex({
    hasFullAccess,
    filter,
    posts,
    series,
    isAuthenticated,
}: Props) {
    const page = usePage<SharedData>();
    const dashboardUrl = page.props.currentTeam
        ? dashboard(page.props.currentTeam.slug)
        : '/';

    const content = (
        <>
            <Head title="Learn" />
            <div className="space-y-8">
                <div>
                    <h1 className="text-3xl font-semibold tracking-tight">Learn</h1>
                    <p className="mt-2 text-muted-foreground">
                        {hasFullAccess
                            ? 'Articles, reminders, and series to help you plan every sweldo.'
                            : 'Free previews from Kinsenas — sign in to read the full library.'}
                    </p>
                </div>

                {hasFullAccess && (
                    <div className="flex flex-wrap gap-2">
                        {filters.map((item) => (
                            <Button
                                key={item.value}
                                variant={filter === item.value ? 'default' : 'outline'}
                                size="sm"
                                onClick={() =>
                                    router.get('/learn', { filter: item.value }, { preserveState: true })
                                }
                            >
                                {item.label}
                            </Button>
                        ))}
                    </div>
                )}

                {series.length > 0 && (
                    <section className="space-y-3">
                        <h2 className="text-lg font-medium">Series</h2>
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
                    </section>
                )}

                <section className="space-y-3">
                    <h2 className="text-lg font-medium">
                        {hasFullAccess ? 'Latest' : 'Free previews'}
                    </h2>
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
                </section>
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
