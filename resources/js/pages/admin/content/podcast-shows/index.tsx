import { Head, Link } from '@inertiajs/react';
import ContentAdminTabs from '@/components/admin/content-admin-tabs';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import type { PodcastShowAdmin } from '@/types/learn-library';

type Props = {
    shows: PodcastShowAdmin[];
};

export default function AdminPodcastShowsIndex({ shows }: Props) {
    return (
        <>
            <Head title="Admin — Podcast shows" />
            <ContentAdminTabs active="podcasts" />
            <div className="mt-4 flex flex-wrap items-center justify-between gap-4">
                <Heading
                    variant="small"
                    title="Podcast shows"
                    description="Manage podcast feeds separately from Learn posts."
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
                        <Button variant="outline" asChild>
                            <Link href={`/admin/content/podcast-shows/${show.slug}/edit`}>Edit</Link>
                        </Button>
                    </li>
                ))}
            </ul>
        </>
    );
}
