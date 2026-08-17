import { Form, Head, Link } from '@inertiajs/react';
import ContentEntityTabs from '@/components/admin/content-entity-tabs';
import TiptapEditor from '@/components/admin/tiptap-editor';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { selectClassName, textareaClassName } from '@/lib/form-field-classes';
import type { PodcastShowAdmin } from '@/types/learn-library';

type Props = {
    showOptions: Array<{ id: string; title: string }>;
    selectedShowId?: string;
    parentShow?: PodcastShowAdmin;
    storeUrl?: string;
};

export default function AdminPodcastEpisodeCreate({
    showOptions,
    selectedShowId,
    parentShow,
    storeUrl,
}: Props) {
    const formAction = storeUrl ?? '/admin/content/podcast-episodes';
    const cancelHref = parentShow
        ? `/admin/content/podcast-shows/${parentShow.slug}/edit`
        : '/admin/content/podcasts';

    return (
        <>
            <Head title="Admin — New podcast episode" />
            <ContentEntityTabs entity="podcasts" section="list" />
            <Heading
                variant="small"
                title={parentShow ? `New episode — ${parentShow.title}` : 'New podcast episode'}
            />
            <Form action={formAction} method="post" className="mt-6 max-w-3xl space-y-4">
                {!storeUrl && (
                    <div className="grid gap-2">
                        <Label htmlFor="podcast_show_id">Show</Label>
                        <select
                            id="podcast_show_id"
                            name="podcast_show_id"
                            className={selectClassName}
                            required
                            defaultValue={selectedShowId ?? ''}
                        >
                            <option value="">Select show</option>
                            {showOptions.map((show) => (
                                <option key={show.id} value={show.id}>
                                    {show.title}
                                </option>
                            ))}
                        </select>
                    </div>
                )}
                <div className="grid gap-2">
                    <Label htmlFor="episode_number">Episode number</Label>
                    <Input id="episode_number" name="episode_number" type="number" min={1} required />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="title">Title</Label>
                    <Input id="title" name="title" required />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="slug">Slug</Label>
                    <Input id="slug" name="slug" required />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="excerpt">Excerpt</Label>
                    <textarea id="excerpt" name="excerpt" className={textareaClassName} />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="post_as">Post as (optional)</Label>
                    <Input id="post_as" name="post_as" placeholder="e.g. Kinsenas Editorial" />
                    <p className="text-xs text-muted-foreground">
                        Shown on the episode page as &quot;By [Post as name]&quot; when set.
                    </p>
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="show_notes">Show notes</Label>
                    <TiptapEditor name="show_notes" />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="audio_embed_url">Audio embed URL</Label>
                    <Input id="audio_embed_url" name="audio_embed_url" type="url" />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="duration_minutes">Duration (minutes)</Label>
                    <Input id="duration_minutes" name="duration_minutes" type="number" min={1} />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="publish_scope">Publish scope</Label>
                    <select id="publish_scope" name="publish_scope" className={selectClassName} defaultValue="internal">
                        <option value="internal">Internal only</option>
                        <option value="external">External only</option>
                        <option value="both">Internal & external</option>
                    </select>
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="status">Status</Label>
                    <select id="status" name="status" className={selectClassName} defaultValue="draft">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
                <div className="flex gap-2">
                    <Button type="submit">Create episode</Button>
                    <Button variant="outline" asChild>
                        <Link href={cancelHref}>Cancel</Link>
                    </Button>
                </div>
            </Form>
        </>
    );
}
