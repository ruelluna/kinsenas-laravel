import { Head, Link } from '@inertiajs/react';
import { AdminEditLink } from '@/components/admin/admin-list-actions';
import ContentEntityTabs from '@/components/admin/content-entity-tabs';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import type { PodcastShowAdmin } from '@/types/learn-library';

type Props = {
    shows: PodcastShowAdmin[];
};

export default function AdminPodcastShowsIndex({ shows }: Props) {
    return (
        <>
            <Head title="Admin — Podcasts" />
            <ContentEntityTabs entity="podcasts" section="list" />
            <div className="mt-4 flex flex-wrap items-center justify-between gap-4">
                <Heading
                    variant="small"
                    title="Podcasts"
                    description="Manage podcast shows and their episodes."
                />
                <Button asChild>
                    <Link href="/admin/content/podcast-shows/create">New show</Link>
                </Button>
            </div>
            <ul className="mt-6 space-y-3">
                {shows.map((show) => (
                    <li
                        key={show.id}
                        className="flex flex-wrap items-center justify-between gap-4 rounded-lg border p-4"
                    >
                        <div>
                            <p className="font-medium">{show.title}</p>
                            <p className="text-sm text-muted-foreground">
                                {show.slug} · {show.episodesCount} episodes · {show.status}
                            </p>
                        </div>
                        <AdminEditLink href={`/admin/content/podcast-shows/${show.slug}/edit`} />
                    </li>
                ))}
            </ul>
        </>
    );
}
