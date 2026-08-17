import { Form, Head, Link } from '@inertiajs/react';
import ContentEntityTabs from '@/components/admin/content-entity-tabs';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { selectClassName, textareaClassName } from '@/lib/form-field-classes';
import type { SideHustleCategoryAdmin } from '@/types/learn-library';

type Props = {
    category: SideHustleCategoryAdmin;
};

export default function AdminSideHustleCategoryEdit({ category }: Props) {
    return (
        <>
            <Head title={`Admin — ${category.name}`} />
            <ContentEntityTabs entity="side-hustles" section="settings" />
            <Heading variant="small" title={category.name} />
            <Form
                action={`/admin/content/side-hustle-categories/${category.slug}`}
                method="put"
                className="mt-6 max-w-2xl space-y-4"
            >
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
                        <option value="archived">Archived</option>
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
                <div className="flex gap-2">
                    <Button type="submit">Save category</Button>
                    <Button variant="outline" asChild>
                        <Link href="/admin/content/side-hustle-categories">Back</Link>
                    </Button>
                </div>
            </Form>
        </>
    );
}
