import { Form, Head, Link } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import ContentPostFormFields from '@/components/admin/content-post-form-fields';

type Props = {
    seriesOptions: Array<{ id: string; title: string }>;
};

export default function AdminContentPostsCreate({ seriesOptions }: Props) {
    return (
        <>
            <Head title="Admin — New post" />
            <Heading variant="small" title="New post" />
            <Form action="/admin/content/posts" method="post" className="mt-6 max-w-3xl space-y-4">
                <ContentPostFormFields seriesOptions={seriesOptions} />
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
