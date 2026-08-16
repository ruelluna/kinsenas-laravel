import { Head, Link } from '@inertiajs/react';
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

                {episodes.length === 0 && (
                    <p className="text-sm text-muted-foreground">No episodes available yet.</p>
                )}
            </div>
        </>
    );

    return isAuthenticated ? content : <LearnMarketingShell>{content}</LearnMarketingShell>;
}
