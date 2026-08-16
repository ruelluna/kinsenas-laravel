import { Form, Head, Link } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { selectClassName, textareaClassName } from '@/lib/form-field-classes';
import type { ContentSeriesAdmin } from '@/types/content';

type Props = {
    series: ContentSeriesAdmin;
};

export default function AdminContentSeriesEdit({ series }: Props) {
    return (
        <>
            <Head title={`Admin — ${series.title}`} />
            <Heading variant="small" title={series.title} description="Edit series metadata." />
            <Form
                action={`/admin/content/series/${series.slug}`}
                method="put"
                className="mt-6 max-w-2xl space-y-4"
            >
                <div className="grid gap-2">
                    <Label htmlFor="title">Title</Label>
                    <Input id="title" name="title" defaultValue={series.title} required />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="slug">Slug</Label>
                    <Input id="slug" name="slug" defaultValue={series.slug} required />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="description">Description</Label>
                    <textarea
                        id="description"
                        name="description"
                        className={textareaClassName}
                        defaultValue={series.description ?? ''}
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="cover_image_url">Cover image URL</Label>
                    <Input
                        id="cover_image_url"
                        name="cover_image_url"
                        type="url"
                        defaultValue={series.coverImageUrl ?? ''}
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="status">Status</Label>
                    <select
                        id="status"
                        name="status"
                        className={selectClassName}
                        defaultValue={series.status}
                    >
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="sort_order">Sort order</Label>
                    <Input
                        id="sort_order"
                        name="sort_order"
                        type="number"
                        defaultValue={series.sortOrder}
                    />
                </div>
                <div className="flex gap-2">
                    <Button type="submit">Save series</Button>
                    <Button variant="outline" asChild>
                        <Link href="/admin/content/series">Back</Link>
                    </Button>
                </div>
            </Form>
        </>
    );
}
