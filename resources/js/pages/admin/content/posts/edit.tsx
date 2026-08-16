import { Form, Head, Link } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import ContentPostFormFields from '@/components/admin/content-post-form-fields';
import type { ContentPostAdmin } from '@/types/content';

type Props = {
    post: ContentPostAdmin;
    seriesOptions: Array<{ id: string; title: string }>;
};

export default function AdminContentPostsEdit({ post, seriesOptions }: Props) {
    return (
        <>
            <Head title={`Admin — ${post.title}`} />
            <Heading variant="small" title={post.title} description="Edit post content and publish settings." />
            <Form
                action={`/admin/content/posts/${post.slug}`}
                method="put"
                className="mt-6 max-w-3xl space-y-4"
            >
                <ContentPostFormFields post={post} seriesOptions={seriesOptions} />
                <div className="flex gap-2">
                    <Button type="submit">Save post</Button>
                    <Button variant="outline" asChild>
                        <Link href={`/admin/content/posts/${post.slug}/preview`}>Preview</Link>
                    </Button>
                    <Button variant="outline" asChild>
                        <Link href="/admin/content/posts">Back</Link>
                    </Button>
                </div>
            </Form>
        </>
    );
}
