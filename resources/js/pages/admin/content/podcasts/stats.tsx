import { Head } from '@inertiajs/react';
import ContentEntityTabs from '@/components/admin/content-entity-tabs';
import Heading from '@/components/heading';

type Props = {
    summary: {
        showCount: number;
        episodeCount: number;
        publishedShows: number;
        publishedEpisodes: number;
    };
};

export default function AdminPodcastsStats({ summary }: Props) {
    return (
        <>
            <Head title="Admin — Podcasts stats" />
            <ContentEntityTabs entity="podcasts" section="stats" />
            <div className="mt-4">
                <Heading
                    variant="small"
                    title="Podcasts stats"
                    description="Show and episode counts."
                />
            </div>
            <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div className="rounded-lg border p-4">
                    <p className="text-sm text-muted-foreground">Shows</p>
                    <p className="text-2xl font-semibold">{summary.showCount}</p>
                </div>
                <div className="rounded-lg border p-4">
                    <p className="text-sm text-muted-foreground">Published shows</p>
                    <p className="text-2xl font-semibold">{summary.publishedShows}</p>
                </div>
                <div className="rounded-lg border p-4">
                    <p className="text-sm text-muted-foreground">Episodes</p>
                    <p className="text-2xl font-semibold">{summary.episodeCount}</p>
                </div>
                <div className="rounded-lg border p-4">
                    <p className="text-sm text-muted-foreground">Published episodes</p>
                    <p className="text-2xl font-semibold">{summary.publishedEpisodes}</p>
                </div>
            </div>
        </>
    );
}
