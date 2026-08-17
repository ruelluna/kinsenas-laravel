import { Form, Head, Link } from '@inertiajs/react';
import ContentAdminTabs from '@/components/admin/content-admin-tabs';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { selectClassName, textareaClassName } from '@/lib/form-field-classes';

type Category = {
    name: string;
    slug: string;
    description: string | null;
    status: string;
    sortOrder: number;
};

type Props = {
    category: Category;
};

export default function AdminPostCategoryEdit({ category }: Props) {
    return (
        <>
            <Head title={`Admin — ${category.name}`} />
            <ContentAdminTabs active="post-categories" />
            <Heading variant="small" title={category.name} />
            <Form
                action={`/admin/content/post-categories/${category.slug}`}
                method="post"
                className="mt-6 max-w-2xl space-y-4"
            >
                <input type="hidden" name="_method" value="put" />
                <div className="grid gap-2">
                    <Label htmlFor="name">Name</Label>
                    <Input id="name" name="name" defaultValue={category.name} required />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="slug">Slug</Label>
                    <Input id="slug" name="slug" defaultValue={category.slug} required />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="description">Description</Label>
                    <textarea
                        id="description"
                        name="description"
                        className={textareaClassName}
                        defaultValue={category.description ?? ''}
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="status">Status</Label>
                    <select
                        id="status"
                        name="status"
                        className={selectClassName}
                        defaultValue={category.status}
                    >
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="sort_order">Sort order</Label>
                    <Input
                        id="sort_order"
                        name="sort_order"
                        type="number"
                        defaultValue={category.sortOrder}
                    />
                </div>
                <Button type="submit">Save category</Button>
            </Form>
        </>
    );
}
