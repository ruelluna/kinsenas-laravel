import { Head, Link } from '@inertiajs/react';
import LearnEmptyState from '@/components/learn/learn-empty-state';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { PaginatedLibrary } from '@/types/learn-library';

type Post = {
    id: string;
    title: string;
    slug: string;
    status: string;
    statusLabel: string;
};

type Props = {
    hasFullAccess: boolean;
    posts: PaginatedLibrary<Post>;
};

export default function LearnCommunityMine({ hasFullAccess, posts }: Props) {
    return (
        <>
            <Head title="My community posts" />
            <div className="space-y-6">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <h1 className="text-3xl font-semibold tracking-tight">My stories</h1>
                    {hasFullAccess && (
                        <Button asChild>
                            <Link href="/learn/community/create">New story</Link>
                        </Button>
                    )}
                </div>
                <div className="grid gap-4">
                    {posts.data.length === 0 ? (
                        <LearnEmptyState
                            title="You have not shared a story yet"
                            description="Submit a story for review — it will appear here while pending and after approval."
                            action={
                                hasFullAccess ? (
                                    <Button asChild>
                                        <Link href="/learn/community/create">Share your story</Link>
                                    </Button>
                                ) : undefined
                            }
                            testId="learn-empty-community-mine"
                        />
                    ) : (
                        posts.data.map((post) => (
                            <Link
                                key={post.id}
                                href={`/learn/community/${post.slug}`}
                                className="block rounded-lg border p-4"
                            >
                                <div className="flex items-center gap-2">
                                    <span className="font-medium">{post.title}</span>
                                    <Badge variant="outline">{post.statusLabel}</Badge>
                                </div>
                            </Link>
                        ))
                    )}
                </div>
            </div>
        </>
    );
}
