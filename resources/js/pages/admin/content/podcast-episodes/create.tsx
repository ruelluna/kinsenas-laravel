import { Form, Head, Link } from '@inertiajs/react';
import ContentAdminTabs from '@/components/admin/content-admin-tabs';
import TiptapEditor from '@/components/admin/tiptap-editor';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const textareaClassName =
    'border-input min-h-24 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none';

type Props = {
    showOptions: Array<{ id: string; title: string }>;
};

export default function AdminPodcastEpisodeCreate({ showOptions }: Props) {
    return (
        <>
            <Head title="Admin — New podcast episode" />
            <ContentAdminTabs active="podcast-episodes" />
            <Heading variant="small" title="New podcast episode" />
            <Form
                action="/admin/content/podcast-episodes"
                method="post"
                className="mt-6 max-w-3xl space-y-4"
            >
                <div className="grid gap-2">
                    <Label htmlFor="podcast_show_id">Show</Label>
                    <select
                        id="podcast_show_id"
                        name="podcast_show_id"
                        className={textareaClassName}
                        required
                    >
                        <option value="">Select show</option>
                        {showOptions.map((show) => (
                            <option key={show.id} value={show.id}>
                                {show.title}
                            </option>
                        ))}
                    </select>
                </div>
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
                    <select id="publish_scope" name="publish_scope" className={textareaClassName} defaultValue="internal">
                        <option value="internal">Internal only</option>
                        <option value="external">External only</option>
                        <option value="both">Internal & external</option>
                    </select>
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="status">Status</Label>
                    <select id="status" name="status" className={textareaClassName} defaultValue="draft">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
                <div className="flex gap-2">
                    <Button type="submit">Create episode</Button>
                    <Button variant="outline" asChild>
                        <Link href="/admin/content/podcast-episodes">Cancel</Link>
                    </Button>
                </div>
            </Form>
        </>
    );
}
