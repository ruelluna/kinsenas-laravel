import { Form, Head, Link } from '@inertiajs/react';
import ContentAdminTabs from '@/components/admin/content-admin-tabs';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import type { PaginatedLibrary } from '@/types/learn-library';

type Post = {
    id: string;
    title: string;
    slug: string;
    status: string;
    statusLabel: string;
    authorName: string | null;
    categoryNames: string;
    publishedAt: string | null;
    createdAt: string | null;
};

type Props = {
    posts: PaginatedLibrary<Post>;
};

export default function AdminCommunityPostsIndex({ posts }: Props) {
    return (
        <>
            <Head title="Admin — Community posts" />
            <ContentAdminTabs active="community-posts" />
            <div className="mt-4 flex flex-wrap items-center justify-between gap-4">
                <Heading
                    variant="small"
                    title="Community posts"
                    description="All member stories — remove published posts from the Learn feed."
                />
                <Button variant="outline" asChild>
                    <Link href="/admin/content/community-posts/pending">Moderation queue</Link>
                </Button>
            </div>
            <ul className="mt-6 space-y-3">
                {posts.data.map((post) => (
                    <li
                        key={post.id}
                        className="flex flex-wrap items-center justify-between gap-4 rounded-lg border p-4"
                    >
                        <div>
                            <p className="font-medium">{post.title}</p>
                            <p className="text-sm text-muted-foreground">
                                {post.slug} · {post.statusLabel} · {post.authorName}
                                {post.categoryNames ? ` · ${post.categoryNames}` : ''}
                            </p>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <Button variant="outline" asChild>
                                <Link href={`/learn/community/${post.slug}`}>View</Link>
                            </Button>
                            {(post.status === 'published' || post.status === 'pending') && (
                                <Form
                                    action={`/admin/content/community-posts/${post.slug}`}
                                    method="delete"
                                >
                                    <Button
                                        type="submit"
                                        size="sm"
                                        variant="outline"
                                        data-test="community-remove-button"
                                    >
                                        Remove
                                    </Button>
                                </Form>
                            )}
                        </div>
                    </li>
                ))}
            </ul>
        </>
    );
}
