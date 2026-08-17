import { Head, Link, router } from '@inertiajs/react';
import { AdminEditLink } from '@/components/admin/admin-list-actions';
import ContentEntityTabs from '@/components/admin/content-entity-tabs';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import type { ContentPostSummary } from '@/types/content';

type Props = {
    window: string;
    summary: {
        totalViews: number;
        uniqueViewers: number;
        totalReactions: number;
    };
    topPosts: Array<{
        post: ContentPostSummary;
        views: number;
        uniqueViewers: number;
        reactions: number;
    }>;
};

export default function AdminPostsStats({ window, summary, topPosts }: Props) {
    return (
        <>
            <Head title="Admin — Posts stats" />
            <ContentEntityTabs entity="posts" section="stats" />
            <div className="mt-4">
                <Heading
                    variant="small"
                    title="Posts stats"
                    description="Views and helpful reactions across Learn posts."
                />
            </div>
            <div className="mt-4 flex flex-wrap gap-2">
                {[
                    { value: 'all', label: 'All time' },
                    { value: '7', label: '7 days' },
                    { value: '30', label: '30 days' },
                ].map((item) => (
                    <Button
                        key={item.value}
                        variant={window === item.value ? 'default' : 'outline'}
                        size="sm"
                        onClick={() =>
                            router.get(
                                '/admin/content/posts/stats',
                                { window: item.value },
                                { preserveState: true },
                            )
                        }
                    >
                        {item.label}
                    </Button>
                ))}
            </div>
            <div className="mt-6 grid gap-4 sm:grid-cols-3">
                <div className="rounded-lg border p-4">
                    <p className="text-sm text-muted-foreground">Total views</p>
                    <p className="text-2xl font-semibold">{summary.totalViews}</p>
                </div>
                <div className="rounded-lg border p-4">
                    <p className="text-sm text-muted-foreground">Unique viewers</p>
                    <p className="text-2xl font-semibold">{summary.uniqueViewers}</p>
                </div>
                <div className="rounded-lg border p-4">
                    <p className="text-sm text-muted-foreground">Helpful reactions</p>
                    <p className="text-2xl font-semibold">{summary.totalReactions}</p>
                </div>
            </div>
            <div className="mt-8">
                <h2 className="text-lg font-medium">Top posts</h2>
                <ul className="mt-3 space-y-3">
                    {topPosts.map((row) => (
                        <li key={row.post.id} className="rounded-lg border p-4">
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p className="font-medium">{row.post.title}</p>
                                    <p className="text-sm text-muted-foreground">
                                        {row.views} views · {row.uniqueViewers} unique · {row.reactions}{' '}
                                        helpful
                                    </p>
                                </div>
                                <AdminEditLink href={`/admin/content/posts/${row.post.slug}/edit`} />
                            </div>
                        </li>
                    ))}
                </ul>
            </div>
        </>
    );
}
