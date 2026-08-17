import { Head, Link } from '@inertiajs/react';
import LearnEmptyState from '@/components/learn/learn-empty-state';
import LearnNavTabs from '@/components/learn/learn-nav-tabs';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { PaginatedLibrary } from '@/types/learn-library';

type Category = {
    id: string;
    name: string;
    slug: string;
};

type Post = {
    id: string;
    title: string;
    slug: string;
    excerpt: string | null;
    status: string;
    statusLabel: string;
    authorName: string;
    authorAvatarUrl: string | null;
    categories: Category[];
};

type Props = {
    hasFullAccess: boolean;
    isAuthenticated: boolean;
    categories: Category[];
    activeCategory: string | null;
    posts: PaginatedLibrary<Post>;
};

export default function LearnCommunityIndex({
    hasFullAccess,
    categories,
    activeCategory,
    posts,
}: Props) {
    return (
        <>
            <Head title="Community" />
            <div className="space-y-8">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-semibold tracking-tight">Community</h1>
                        <p className="mt-2 text-muted-foreground">
                            Stories and tips from Kinsenas members.
                        </p>
                    </div>
                    {hasFullAccess && (
                        <Button asChild>
                            <Link href="/learn/community/create">Share your story</Link>
                        </Button>
                    )}
                </div>

                <LearnNavTabs filter="community" hasFullAccess={hasFullAccess} />

                <div className="grid gap-4">
                    {posts.data.length === 0 ? (
                        <LearnEmptyState
                            title="No community stories yet"
                            description={
                                hasFullAccess
                                    ? 'Be the first to share a payday win, side hustle story, or tip.'
                                    : 'Subscribe to read and share stories from other members.'
                            }
                            action={
                                hasFullAccess ? (
                                    <Button asChild>
                                        <Link href="/learn/community/create">Share your story</Link>
                                    </Button>
                                ) : undefined
                            }
                            testId="learn-empty-community"
                        />
                    ) : (
                        posts.data.map((post) => (
                            <Link
                                key={post.id}
                                href={`/learn/community/${post.slug}`}
                                className="block rounded-lg border p-4 transition hover:border-primary/40"
                            >
                                <div className="flex flex-wrap gap-2">
                                    {post.categories.map((category) => (
                                        <Badge key={category.id} variant="secondary">
                                            {category.name}
                                        </Badge>
                                    ))}
                                </div>
                                <h2 className="mt-2 font-medium">{post.title}</h2>
                                {post.excerpt && (
                                    <p className="mt-1 text-sm text-muted-foreground">{post.excerpt}</p>
                                )}
                                <p className="mt-2 text-xs text-muted-foreground">By {post.authorName}</p>
                            </Link>
                        ))
                    )}
                </div>

                {!hasFullAccess && posts.data.length > 0 && (
                    <p className="text-sm text-muted-foreground">
                        Subscribe to read and share community stories.
                    </p>
                )}
            </div>
        </>
    );
}
