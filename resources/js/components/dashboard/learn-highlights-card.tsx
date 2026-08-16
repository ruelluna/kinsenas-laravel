import { Link } from '@inertiajs/react';
import { BookOpen } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import type { ContentPostSummary } from '@/types/content';

type Props = {
    posts: ContentPostSummary[];
};

export default function LearnHighlightsCard({ posts }: Props) {
    if (posts.length === 0) {
        return null;
    }

    return (
        <section className="rounded-lg border p-4" data-test="learn-highlights-card">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <div className="flex items-center gap-2">
                    <BookOpen className="size-4 text-muted-foreground" />
                    <h2 className="font-medium">New on Learn</h2>
                </div>
                <Link
                    href="/learn"
                    className="text-sm text-primary underline-offset-4 hover:underline"
                >
                    Browse all
                </Link>
            </div>
            <ul className="mt-4 flex flex-col gap-1">
                {posts.map((post) => (
                    <li key={post.id}>
                        <Link
                            href={`/learn/posts/${post.slug}`}
                            className="-mx-2 block rounded-md p-2 hover:bg-muted/50"
                        >
                            <div className="flex flex-wrap items-center gap-2">
                                <span className="font-medium">{post.title}</span>
                                <Badge variant="secondary" className="text-xs">
                                    {post.contentTypeLabel}
                                </Badge>
                            </div>
                            {post.excerpt && (
                                <p className="mt-1 line-clamp-2 text-sm text-muted-foreground">
                                    {post.excerpt}
                                </p>
                            )}
                        </Link>
                    </li>
                ))}
            </ul>
        </section>
    );
}
