import { Head, Link } from '@inertiajs/react';
import { AdminRowActions } from '@/components/admin/admin-list-actions';
import ContentEntityTabs from '@/components/admin/content-entity-tabs';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import type { ContentPostAdmin, PaginatedPosts } from '@/types/content';

type PostRow = ContentPostAdmin & { helpfulCount: number };

type Props = {
    posts: PaginatedPosts<PostRow>;
};

export default function AdminContentPostsIndex({ posts }: Props) {
    return (
        <>
            <Head title="Admin — Content posts" />
            <ContentEntityTabs entity="posts" section="list" />
            <div className="mt-4 flex flex-wrap items-center justify-between gap-4">
                <Heading
                    variant="small"
                    title="Content posts"
                    description="Articles, reminders, shares, and series episodes."
                />
                <Button asChild>
                    <Link href="/admin/content/posts/create">New post</Link>
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
                                {post.slug} · {post.contentTypeLabel} · {post.publishScope} ·{' '}
                                {post.status} · {post.authorName} · {post.helpfulCount} helpful
                            </p>
                        </div>
                        <AdminRowActions
                            previewHref={`/admin/content/posts/${post.slug}/preview`}
                            editHref={`/admin/content/posts/${post.slug}/edit`}
                            deleteAction={`/admin/content/posts/${post.slug}`}
                            deleteTestId="content-post-delete-button"
                        />
                    </li>
                ))}
            </ul>
        </>
    );
}
