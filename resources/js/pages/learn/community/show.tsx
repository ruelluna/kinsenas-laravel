import { Form, Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import ContentBody from '@/components/content/content-body';
import ContentByline from '@/components/content/content-byline';
import { Badge } from '@/components/ui/badge';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';

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
    const [reportOpen, setReportOpen] = useState(false);

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
                    <Collapsible open={reportOpen} onOpenChange={setReportOpen}>
                        <CollapsibleTrigger
                            className="text-xs text-muted-foreground hover:text-foreground"
                            data-test="community-report-toggle"
                        >
                            Report this post
                        </CollapsibleTrigger>
                        <CollapsibleContent className="mt-3 space-y-3 rounded-lg border border-dashed p-4">
                            <p className="text-sm text-muted-foreground">
                                Flag content that breaks community guidelines. Reports are reviewed by
                                admins.
                            </p>
                            <Form
                                action={`/learn/community/${post.slug}/report`}
                                method="post"
                                className="space-y-3"
                            >
                                <select
                                    name="reason"
                                    className="w-full rounded-md border border-input px-3 py-2 text-sm"
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
                                    className="text-sm font-medium text-foreground underline-offset-4 hover:underline"
                                    data-test="community-report-button"
                                >
                                    Submit report
                                </button>
                            </Form>
                        </CollapsibleContent>
                    </Collapsible>
                )}
            </article>
        </>
    );
}
