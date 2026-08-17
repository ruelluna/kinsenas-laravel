import { Form, Head, Link } from '@inertiajs/react';
import { AdminEditLink } from '@/components/admin/admin-list-actions';
import ContentEntityTabs from '@/components/admin/content-entity-tabs';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { selectClassName, textareaClassName } from '@/lib/form-field-classes';
import type { PodcastEpisodeAdmin, PodcastShowAdmin } from '@/types/learn-library';

type Props = {
    show: PodcastShowAdmin;
    episodes: PodcastEpisodeAdmin[];
};

export default function AdminPodcastShowEdit({ show, episodes }: Props) {
    return (
        <>
            <Head title={`Admin — ${show.title}`} />
            <ContentEntityTabs entity="podcasts" section="list" />
            <Heading variant="small" title={show.title} />
            <Form
                action={`/admin/content/podcast-shows/${show.slug}`}
                method="put"
                className="mt-6 max-w-2xl space-y-4"
            >
                <div className="grid gap-2">
                    <Label htmlFor="title">Title</Label>
                    <Input id="title" name="title" defaultValue={show.title} required />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="slug">Slug</Label>
                    <Input id="slug" name="slug" defaultValue={show.slug} required />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="description">Description</Label>
                    <textarea
                        id="description"
                        name="description"
                        className={textareaClassName}
                        defaultValue={show.description ?? ''}
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="cover_image_url">Cover image URL</Label>
                    <Input
                        id="cover_image_url"
                        name="cover_image_url"
                        type="url"
                        defaultValue={show.coverImageUrl ?? ''}
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="status">Status</Label>
                    <select id="status" name="status" className={selectClassName} defaultValue={show.status}>
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="sort_order">Sort order</Label>
                    <Input id="sort_order" name="sort_order" type="number" defaultValue={show.sortOrder} />
                </div>
                <div className="flex gap-2">
                    <Button type="submit">Save show</Button>
                    <Button variant="outline" asChild>
                        <Link href="/admin/content/podcasts">Back</Link>
                    </Button>
                </div>
            </Form>

            <section className="mt-10 max-w-2xl">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <h2 className="text-lg font-medium">Episodes</h2>
                    <Button asChild size="sm">
                        <Link href={`/admin/content/podcasts/${show.slug}/episodes/create`}>
                            Add episode
                        </Link>
                    </Button>
                </div>
                <ul className="mt-4 space-y-3">
                    {episodes.length === 0 && (
                        <li className="text-sm text-muted-foreground">No episodes yet.</li>
                    )}
                    {episodes.map((episode) => (
                        <li
                            key={episode.id}
                            className="flex flex-wrap items-center justify-between gap-4 rounded-lg border p-4"
                        >
                            <div>
                                <p className="font-medium">
                                    #{episode.episodeNumber} · {episode.title}
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    {episode.slug} · {episode.status}
                                </p>
                            </div>
                            <AdminEditLink
                                href={`/admin/content/podcast-episodes/${episode.slug}/edit`}
                            />
                        </li>
                    ))}
                </ul>
            </section>
        </>
    );
}
