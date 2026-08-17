import { Head, Link } from '@inertiajs/react';
import LearnEmptyState from '@/components/learn/learn-empty-state';
import LearnMarketingShell from '@/components/learn/learn-marketing-shell';
import { Button } from '@/components/ui/button';
import type { PodcastEpisodeSummary, PodcastShowSummary } from '@/types/learn-library';

type Props = {
    show: PodcastShowSummary;
    episodes: PodcastEpisodeSummary[];
    hasFullAccess: boolean;
    isAuthenticated: boolean;
};

export default function LearnPodcastShow({
    show,
    episodes,
    hasFullAccess,
    isAuthenticated,
}: Props) {
    const content = (
        <>
            <Head title={show.title} />
            <div className="space-y-8">
                <div className="space-y-3">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href="/learn?filter=podcasts">← Podcasts</Link>
                    </Button>
                    <h1 className="text-3xl font-semibold tracking-tight">{show.title}</h1>
                    {show.description && (
                        <p className="text-lg text-muted-foreground">{show.description}</p>
                    )}
                </div>

                {episodes.length === 0 ? (
                    <LearnEmptyState
                        title="No episodes yet"
                        description="New episodes will show up here when they are published."
                        testId="learn-empty-podcast-episodes"
                    />
                ) : (
                    <ul className="space-y-3">
                        {episodes.map((episode) => (
                            <li key={episode.id}>
                                <Link
                                    href={`/learn/podcasts/${show.slug}/episodes/${episode.slug}`}
                                    className="block rounded-lg border p-4 transition hover:border-primary/40"
                                >
                                    <p className="text-sm text-muted-foreground">
                                        Episode {episode.episodeNumber}
                                        {episode.durationMinutes
                                            ? ` · ${episode.durationMinutes} min`
                                            : ''}
                                    </p>
                                    <p className="mt-1 font-medium">{episode.title}</p>
                                    {episode.excerpt && (
                                        <p className="mt-2 text-sm text-muted-foreground">
                                            {episode.excerpt}
                                        </p>
                                    )}
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </>
    );

    return isAuthenticated ? content : <LearnMarketingShell>{content}</LearnMarketingShell>;
}
