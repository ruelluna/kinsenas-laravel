import { Head, Link } from '@inertiajs/react';
import ContentAdminTabs from '@/components/admin/content-admin-tabs';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import type { PaginatedLibrary, PodcastEpisodeAdmin } from '@/types/learn-library';

type Props = {
    episodes: PaginatedLibrary<PodcastEpisodeAdmin>;
};

export default function AdminPodcastEpisodesIndex({ episodes }: Props) {
    return (
        <>
            <Head title="Admin — Podcast episodes" />
            <ContentAdminTabs active="podcast-episodes" />
            <div className="mt-4 flex flex-wrap items-center justify-between gap-4">
                <Heading
                    variant="small"
                    title="Podcast episodes"
                    description="Episodes belong to a podcast show — separate from Learn posts."
                />
                <Button asChild>
                    <Link href="/admin/content/podcast-episodes/create">New episode</Link>
                </Button>
            </div>
            <ul className="mt-6 space-y-3">
                {episodes.data.map((episode) => (
                    <li
                        key={episode.id}
                        className="flex flex-wrap items-center justify-between gap-4 rounded-lg border p-4"
                    >
                        <div>
                            <p className="font-medium">
                                {episode.show?.title ?? 'Show'} · Ep {episode.episodeNumber}:{' '}
                                {episode.title}
                            </p>
                            <p className="text-sm text-muted-foreground">
                                {episode.slug} · {episode.status}
                            </p>
                        </div>
                        <Button variant="outline" asChild>
                            <Link href={`/admin/content/podcast-episodes/${episode.slug}/edit`}>
                                Edit
                            </Link>
                        </Button>
                    </li>
                ))}
            </ul>
        </>
    );
}
