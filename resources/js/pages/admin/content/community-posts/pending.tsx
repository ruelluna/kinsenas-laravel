import { Form, Head } from '@inertiajs/react';
import ContentAdminTabs from '@/components/admin/content-admin-tabs';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { PaginatedLibrary } from '@/types/learn-library';

type PendingPost = {
    id: string;
    title: string;
    slug: string;
    authorName: string | null;
    categoryName: string | null;
};

type Props = {
    posts: PaginatedLibrary<PendingPost>;
};

export default function AdminCommunityPostsPending({ posts }: Props) {
    return (
        <>
            <Head title="Admin — Community moderation" />
            <ContentAdminTabs active="community-moderation" />
            <h1 className="text-2xl font-semibold">Community moderation</h1>
            <div className="mt-6 space-y-4">
                {posts.data.map((post) => (
                    <div key={post.id} className="rounded-lg border p-4">
                        <p className="font-medium">{post.title}</p>
                        <p className="text-sm text-muted-foreground">
                            {post.authorName} · {post.categoryName}
                        </p>
                        <div className="mt-3 flex flex-wrap gap-2">
                            <Form
                                action={`/admin/content/community-posts/${post.slug}/approve`}
                                method="post"
                            >
                                <Button type="submit" size="sm" data-test="community-approve-button">
                                    Approve
                                </Button>
                            </Form>
                            <Form
                                action={`/admin/content/community-posts/${post.slug}/reject`}
                                method="post"
                                className="flex flex-wrap items-end gap-2"
                            >
                                <div className="grid gap-1">
                                    <Label htmlFor={`reason-${post.id}`}>Rejection reason</Label>
                                    <Input
                                        id={`reason-${post.id}`}
                                        name="rejection_reason"
                                        required
                                    />
                                </div>
                                <Button type="submit" size="sm" variant="outline">
                                    Reject
                                </Button>
                            </Form>
                        </div>
                    </div>
                ))}
            </div>
        </>
    );
}
