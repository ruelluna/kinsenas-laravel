import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { ContentPostAdmin } from '@/types/content';

const textareaClassName =
    'border-input min-h-24 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none';

type Props = {
    post?: ContentPostAdmin;
    seriesOptions: Array<{ id: string; title: string }>;
};

export default function ContentPostFormFields({ post, seriesOptions }: Props) {
    return (
        <>
            <div className="grid gap-2">
                <Label htmlFor="title">Title</Label>
                <Input id="title" name="title" defaultValue={post?.title ?? ''} required />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="slug">Slug</Label>
                <Input id="slug" name="slug" defaultValue={post?.slug ?? ''} required />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="content_series_id">Series (optional)</Label>
                <select
                    id="content_series_id"
                    name="content_series_id"
                    className={textareaClassName}
                    defaultValue={post?.contentSeriesId ?? ''}
                >
                    <option value="">Standalone post</option>
                    {seriesOptions.map((series) => (
                        <option key={series.id} value={series.id}>
                            {series.title}
                        </option>
                    ))}
                </select>
            </div>
            <div className="grid gap-2">
                <Label htmlFor="episode_number">Episode number</Label>
                <Input
                    id="episode_number"
                    name="episode_number"
                    type="number"
                    min={1}
                    defaultValue={post?.episodeNumber ?? ''}
                />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="content_type">Content type</Label>
                <select
                    id="content_type"
                    name="content_type"
                    className={textareaClassName}
                    defaultValue={post?.contentType ?? 'article'}
                >
                    <option value="article">Article</option>
                    <option value="reminder">Reminder</option>
                    <option value="share">Share</option>
                    <option value="episode">Episode</option>
                </select>
            </div>
            <div className="grid gap-2">
                <Label htmlFor="publish_scope">Publish scope</Label>
                <select
                    id="publish_scope"
                    name="publish_scope"
                    className={textareaClassName}
                    defaultValue={post?.publishScope ?? 'internal'}
                >
                    <option value="internal">Internal only</option>
                    <option value="external">External only</option>
                    <option value="both">Internal & external</option>
                </select>
            </div>
            <div className="grid gap-2">
                <Label htmlFor="status">Status</Label>
                <select
                    id="status"
                    name="status"
                    className={textareaClassName}
                    defaultValue={post?.status ?? 'draft'}
                >
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
            <div className="grid gap-2">
                <Label htmlFor="excerpt">Excerpt (required for external/both)</Label>
                <textarea
                    id="excerpt"
                    name="excerpt"
                    className={textareaClassName}
                    defaultValue={post?.excerpt ?? ''}
                />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="body">Body (markdown)</Label>
                <textarea
                    id="body"
                    name="body"
                    className={`${textareaClassName} min-h-64 font-mono`}
                    defaultValue={post?.body ?? ''}
                    required
                />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="cover_image_url">Cover image URL</Label>
                <Input
                    id="cover_image_url"
                    name="cover_image_url"
                    type="url"
                    defaultValue={post?.coverImageUrl ?? ''}
                />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="video_embed_url">Video embed URL</Label>
                <Input
                    id="video_embed_url"
                    name="video_embed_url"
                    type="url"
                    defaultValue={post?.videoEmbedUrl ?? ''}
                />
            </div>
        </>
    );
}
