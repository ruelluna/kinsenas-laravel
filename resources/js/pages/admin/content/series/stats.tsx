import { Head, router } from '@inertiajs/react';
import ContentEntityTabs from '@/components/admin/content-entity-tabs';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import type { ContentSeriesAdmin } from '@/types/content';

type Props = {
    window: string;
    summary: {
        seriesCount: number;
        totalEpisodes: number;
    };
    seriesRows: Array<{
        series: ContentSeriesAdmin;
        postsCount: number;
        views: number;
    }>;
};

export default function AdminSeriesStats({ window, summary, seriesRows }: Props) {
    return (
        <>
            <Head title="Admin — Series stats" />
            <ContentEntityTabs entity="series" section="stats" />
            <div className="mt-4">
                <Heading
                    variant="small"
                    title="Series stats"
                    description="Series count and aggregated views from episode posts."
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
                                '/admin/content/series/stats',
                                { window: item.value },
                                { preserveState: true },
                            )
                        }
                    >
                        {item.label}
                    </Button>
                ))}
            </div>
            <div className="mt-6 grid gap-4 sm:grid-cols-2">
                <div className="rounded-lg border p-4">
                    <p className="text-sm text-muted-foreground">Series</p>
                    <p className="text-2xl font-semibold">{summary.seriesCount}</p>
                </div>
                <div className="rounded-lg border p-4">
                    <p className="text-sm text-muted-foreground">Episode posts</p>
                    <p className="text-2xl font-semibold">{summary.totalEpisodes}</p>
                </div>
            </div>
            <div className="mt-8">
                <h2 className="text-lg font-medium">Views by series</h2>
                <ul className="mt-3 space-y-3">
                    {seriesRows.map((row) => (
                        <li key={row.series.id} className="rounded-lg border p-4">
                            <p className="font-medium">{row.series.title}</p>
                            <p className="text-sm text-muted-foreground">
                                {row.postsCount} posts · {row.views} views
                            </p>
                        </li>
                    ))}
                </ul>
            </div>
        </>
    );
}
