import { Form, Head, Link } from '@inertiajs/react';
import ContentAdminTabs from '@/components/admin/content-admin-tabs';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { PodcastShowAdmin } from '@/types/learn-library';

const textareaClassName =
    'border-input min-h-24 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none';

type Props = {
    show: PodcastShowAdmin;
};

export default function AdminPodcastShowEdit({ show }: Props) {
    return (
        <>
            <Head title={`Admin — ${show.title}`} />
            <ContentAdminTabs active="podcasts" />
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
                    <select id="status" name="status" className={textareaClassName} defaultValue={show.status}>
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
                        <Link href="/admin/content/podcast-shows">Back</Link>
                    </Button>
                </div>
            </Form>
        </>
    );
}
