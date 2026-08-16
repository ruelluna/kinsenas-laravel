import { Link } from '@inertiajs/react';
import ContentBody from '@/components/content/content-body';
import ContentByline from '@/components/content/content-byline';
import LearnPageHead from '@/components/learn/learn-page-head';
import LearnMarketingShell from '@/components/learn/learn-marketing-shell';
import { Button } from '@/components/ui/button';
import type { PodcastEpisodeSummary } from '@/types/learn-library';

type Props = {
    episode: PodcastEpisodeSummary;
    showFullBody: boolean;
    hasFullAccess: boolean;
    isAuthenticated: boolean;
    openGraph?: {
        title: string;
        description: string;
        url: string;
        image: string | null;
    } | null;
};

export default function LearnPodcastEpisodeShow({
    episode,
    showFullBody,
    hasFullAccess,
    isAuthenticated,
    openGraph = null,
}: Props) {
    const showSlug = episode.show?.slug;

    const content = (
        <>
            <LearnPageHead title={episode.title} description={episode.excerpt} openGraph={openGraph} />
            <div className="space-y-8">
                <div className="space-y-3">
                    {showSlug && (
                        <Button variant="ghost" size="sm" asChild>
                            <Link href={`/learn/podcasts/${showSlug}`}>← {episode.show?.title}</Link>
                        </Button>
                    )}
                    <p className="text-sm text-muted-foreground">
                        Episode {episode.episodeNumber}
                        {episode.durationMinutes ? ` · ${episode.durationMinutes} min` : ''}
                    </p>
                    <h1 className="text-3xl font-semibold tracking-tight">{episode.title}</h1>
                    {episode.bylineName && <ContentByline name={episode.bylineName} />}
                    {episode.excerpt && (
                        <p className="text-lg text-muted-foreground">{episode.excerpt}</p>
                    )}
                </div>

                {showFullBody && episode.audioEmbedUrl && (
                    <div className="aspect-video w-full overflow-hidden rounded-lg border">
                        <iframe
                            src={episode.audioEmbedUrl}
                            title={episode.title}
                            className="h-full w-full"
                            allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                            loading="lazy"
                        />
                    </div>
                )}

                {showFullBody && episode.showNotes ? (
                    <ContentBody content={episode.showNotes ?? ''} />
                ) : (
                    <div className="rounded-lg border border-dashed p-6 text-center">
                        <p className="text-muted-foreground">
                            {isAuthenticated
                                ? 'Subscribe to listen and read full show notes.'
                                : 'Sign in and subscribe for the full episode.'}
                        </p>
                        {!isAuthenticated && (
                            <Button className="mt-4" asChild>
                                <Link href="/login">Sign in</Link>
                            </Button>
                        )}
                    </div>
                )}
            </div>
        </>
    );

    return isAuthenticated ? content : <LearnMarketingShell>{content}</LearnMarketingShell>;
}
