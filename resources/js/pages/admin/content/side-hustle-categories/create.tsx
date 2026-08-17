import { Form, Head, Link } from '@inertiajs/react';
import ContentEntityTabs from '@/components/admin/content-entity-tabs';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { selectClassName, textareaClassName } from '@/lib/form-field-classes';

export default function AdminSideHustleCategoryCreate() {
    return (
        <>
            <Head title="Admin — New hustle category" />
            <ContentEntityTabs entity="side-hustles" section="settings" />
            <Heading variant="small" title="New hustle category" />
            <Form
                action="/admin/content/side-hustle-categories"
                method="post"
                className="mt-6 max-w-2xl space-y-4"
            >
                <div className="grid gap-2">
                    <Label htmlFor="name">Name</Label>
                    <Input id="name" name="name" required />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="slug">Slug</Label>
                    <Input id="slug" name="slug" required />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="description">Description</Label>
                    <textarea id="description" name="description" className={textareaClassName} />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="status">Status</Label>
                    <select id="status" name="status" className={selectClassName} defaultValue="draft">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="sort_order">Sort order</Label>
                    <Input id="sort_order" name="sort_order" type="number" defaultValue={0} />
                </div>
                <div className="flex gap-2">
                    <Button type="submit">Create category</Button>
                    <Button variant="outline" asChild>
                        <Link href="/admin/content/side-hustles/settings">Cancel</Link>
                    </Button>
                </div>
            </Form>
        </>
    );
}
