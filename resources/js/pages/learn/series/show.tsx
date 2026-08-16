import { Head, Link, usePage } from '@inertiajs/react';
import LearnMarketingShell from '@/components/learn/learn-marketing-shell';
import { Badge } from '@/components/ui/badge';
import { dashboard } from '@/routes';
import type { ContentPostSummary, ContentSeriesSummary } from '@/types/content';
import type { SharedData } from '@/types';

type Episode = ContentPostSummary & { isRead: boolean };

type Props = {
    hasFullAccess: boolean;
    series: ContentSeriesSummary;
    episodes: Episode[];
    isAuthenticated: boolean;
};

export default function LearnSeriesShow({
    hasFullAccess,
    series,
    episodes,
    isAuthenticated,
}: Props) {
    const page = usePage<SharedData>();
    const dashboardUrl = page.props.currentTeam
        ? dashboard(page.props.currentTeam.slug)
        : '/';

    const content = (
        <>
            <Head title={series.title} />
            <div className="space-y-6">
                <div>
                    <Link href="/learn" className="text-sm text-muted-foreground hover:text-foreground">
                        ← Learn
                    </Link>
                    <h1 className="mt-2 text-3xl font-semibold tracking-tight">{series.title}</h1>
                    {series.description && (
                        <p className="mt-2 text-muted-foreground">{series.description}</p>
                    )}
                </div>
                <ol className="space-y-3">
                    {episodes.map((episode) => (
                        <li key={episode.id}>
                            <Link
                                href={`/learn/posts/${episode.slug}`}
                                className="flex items-start justify-between gap-4 rounded-lg border p-4 transition hover:border-primary/40"
                            >
                                <div>
                                    <p className="font-medium">
                                        {episode.episodeNumber
                                            ? `Episode ${episode.episodeNumber}: `
                                            : ''}
                                        {episode.title}
                                    </p>
                                    {episode.excerpt && (
                                        <p className="mt-1 text-sm text-muted-foreground line-clamp-2">
                                            {episode.excerpt}
                                        </p>
                                    )}
                                </div>
                                {hasFullAccess && episode.isRead && (
                                    <Badge variant="outline">Read</Badge>
                                )}
                            </Link>
                        </li>
                    ))}
                </ol>
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
