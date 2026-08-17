import { Form, Head, Link } from '@inertiajs/react';
import ContentPostFormFields from '@/components/admin/content-post-form-fields';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';

type AuthorOption = {
    id: number;
    name: string;
};

type Props = {
    seriesOptions: Array<{ id: string; title: string }>;
    categoryOptions: Array<{ id: string; name: string }>;
    authorOptions: AuthorOption[];
    canAssignAuthor: boolean;
};

export default function AdminContentPostsCreate({
    seriesOptions,
    categoryOptions,
    authorOptions,
    canAssignAuthor,
}: Props) {
    return (
        <>
            <Head title="Admin — New post" />
            <Heading variant="small" title="New post" />
            <Form action="/admin/content/posts" method="post" className="mt-6 max-w-3xl space-y-4">
                <ContentPostFormFields
                    seriesOptions={seriesOptions}
                    categoryOptions={categoryOptions}
                    authorOptions={authorOptions}
                    canAssignAuthor={canAssignAuthor}
                />
                <div className="flex gap-2">
                    <Button type="submit">Create post</Button>
                    <Button variant="outline" asChild>
                        <Link href="/admin/content/posts">Cancel</Link>
                    </Button>
                </div>
            </Form>
        </>
    );
}
