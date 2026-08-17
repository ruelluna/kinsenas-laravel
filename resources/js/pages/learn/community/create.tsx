import { Form, Head, Link } from '@inertiajs/react';
import CategoryCheckboxGroup from '@/components/admin/category-checkbox-group';
import CoverImageField from '@/components/admin/cover-image-field';
import TiptapEditor from '@/components/admin/tiptap-editor';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Category = {
    id: string;
    name: string;
};

type Props = {
    categories: Category[];
};

export default function LearnCommunityCreate({ categories }: Props) {
    return (
        <>
            <Head title="Share a story" />
            <div className="mx-auto max-w-3xl space-y-6">
                <div>
                    <h1 className="text-3xl font-semibold tracking-tight">Share your story</h1>
                    <p className="mt-2 text-muted-foreground">
                        Submissions are reviewed before they appear in the Community feed.
                    </p>
                </div>
                <Form action="/learn/community" method="post" className="space-y-4">
                    <CategoryCheckboxGroup categories={categories} />
                    <div className="grid gap-2">
                        <Label htmlFor="title">Title</Label>
                        <Input id="title" name="title" required />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="excerpt">Short summary</Label>
                        <Input id="excerpt" name="excerpt" />
                    </div>
                    <CoverImageField />
                    <div className="grid gap-2">
                        <Label htmlFor="body">Story</Label>
                        <TiptapEditor name="body" required />
                    </div>
                    <div className="flex gap-2">
                        <Button type="submit">Submit for review</Button>
                        <Button type="button" variant="outline" asChild>
                            <Link href="/learn/community">Cancel</Link>
                        </Button>
                    </div>
                </Form>
            </div>
        </>
    );
}
