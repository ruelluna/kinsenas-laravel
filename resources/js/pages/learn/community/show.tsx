import { Form, Head, Link } from '@inertiajs/react';
import ContentBody from '@/components/content/content-body';
import ContentByline from '@/components/content/content-byline';
import { Badge } from '@/components/ui/badge';

type Post = {
    title: string;
    slug: string;
    excerpt: string | null;
    body?: string;
    status: string;
    statusLabel: string;
    rejectionReason: string | null;
    authorName: string;
    authorAvatarUrl: string | null;
    categories: Array<{ id: string; name: string }>;
    coverImageUrl: string | null;
};

type Props = {
    post: Post;
    hasFullAccess: boolean;
    canReport: boolean;
};

export default function LearnCommunityShow({ post, canReport }: Props) {
    return (
        <>
            <Head title={post.title} />
            <article className="mx-auto max-w-3xl space-y-6">
                <Link href="/learn/community" className="text-sm text-muted-foreground hover:text-foreground">
                    ← Community
                </Link>
                <div className="flex flex-wrap gap-2">
                    {post.categories.map((category) => (
                        <Badge key={category.id} variant="secondary">
                            {category.name}
                        </Badge>
                    ))}
                </div>
                <h1 className="text-3xl font-semibold tracking-tight">{post.title}</h1>
                <ContentByline name={post.authorName} avatarUrl={post.authorAvatarUrl} />
                {post.status !== 'published' && (
                    <Badge variant="outline">{post.statusLabel}</Badge>
                )}
                {post.rejectionReason && (
                    <p className="text-sm text-destructive">{post.rejectionReason}</p>
                )}
                {post.coverImageUrl && (
                    <img
                        src={post.coverImageUrl}
                        alt=""
                        className="aspect-[2/1] w-full rounded-lg border object-cover"
                    />
                )}
                {post.body && <ContentBody content={post.body} />}
                {canReport && (
                    <Form
                        action={`/learn/community/${post.slug}/report`}
                        method="post"
                        className="border-t pt-6 space-y-3"
                    >
                        <p className="text-sm font-medium">Report this post</p>
                        <select
                            name="reason"
                            className="rounded-md border border-input px-3 py-2 text-sm"
                            required
                            data-test="community-report-reason"
                        >
                            <option value="">Select a reason</option>
                            <option value="spam">Spam</option>
                            <option value="harassment">Harassment or abuse</option>
                            <option value="misinformation">Misinformation</option>
                            <option value="other">Other</option>
                        </select>
                        <textarea
                            name="details"
                            placeholder="Optional details"
                            className="min-h-20 w-full rounded-md border border-input px-3 py-2 text-sm"
                        />
                        <button
                            type="submit"
                            className="text-sm text-muted-foreground underline hover:text-foreground"
                            data-test="community-report-button"
                        >
                            Submit report
                        </button>
                    </Form>
                )}
            </article>
        </>
    );
}
