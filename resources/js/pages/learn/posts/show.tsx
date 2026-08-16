import { Head, Link, usePage } from '@inertiajs/react';
import ContentBody from '@/components/content/content-body';
import LearnMarketingShell from '@/components/learn/learn-marketing-shell';
import VideoEmbed from '@/components/savings/video-embed';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import type { ContentPostSummary } from '@/types/content';
import type { SharedData } from '@/types';
import { ThumbsUp } from 'lucide-react';
import { useState } from 'react';

type Props = {
    post: ContentPostSummary;
    showFullBody: boolean;
    hasFullAccess: boolean;
    helpfulCount: number;
    hasReacted: boolean;
    previousEpisode: ContentPostSummary | null;
    nextEpisode: ContentPostSummary | null;
    isAuthenticated: boolean;
    isPreview?: boolean;
    openGraph?: {
        title: string;
        description: string;
        url: string;
        image: string | null;
    } | null;
};

export default function LearnPostShow({
    post,
    showFullBody,
    hasFullAccess,
    helpfulCount: initialHelpfulCount,
    hasReacted: initialHasReacted,
    previousEpisode,
    nextEpisode,
    isAuthenticated,
    isPreview = false,
    openGraph = null,
}: Props) {
    const page = usePage<SharedData>();
    const dashboardUrl = page.props.currentTeam
        ? dashboard(page.props.currentTeam.slug)
        : '/';
    const [helpfulCount, setHelpfulCount] = useState(initialHelpfulCount);
    const [hasReacted, setHasReacted] = useState(initialHasReacted);
    const [reacting, setReacting] = useState(false);

    async function toggleHelpful() {
        if (!hasFullAccess || reacting) {
            return;
        }

        setReacting(true);

        try {
            const response = await fetch(`/learn/posts/${post.slug}/react`, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN':
                        document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
                            ?.content ?? '',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const data = (await response.json()) as { reacted: boolean; count: number };
            setHasReacted(data.reacted);
            setHelpfulCount(data.count);
        } finally {
            setReacting(false);
        }
    }

    const content = (
        <>
            <Head title={post.title}>
                {post.excerpt && (
                    <meta head-key="description" name="description" content={post.excerpt} />
                )}
                {openGraph && (
                    <>
                        <meta head-key="og:title" property="og:title" content={openGraph.title} />
                        <meta
                            head-key="og:description"
                            property="og:description"
                            content={openGraph.description}
                        />
                        <meta head-key="og:url" property="og:url" content={openGraph.url} />
                        <meta head-key="og:type" property="og:type" content="article" />
                        {openGraph.image && (
                            <meta head-key="og:image" property="og:image" content={openGraph.image} />
                        )}
                    </>
                )}
            </Head>
            <article className="space-y-6">
                {isPreview && (
                    <Badge variant="outline">Admin preview — not published view</Badge>
                )}
                <div className="space-y-2">
                    <div className="flex flex-wrap items-center gap-2">
                        <Badge variant="secondary">{post.contentTypeLabel}</Badge>
                        {post.series && (
                            <Link
                                href={`/learn/series/${post.series.slug}`}
                                className="text-sm text-muted-foreground hover:text-foreground"
                            >
                                {post.series.title}
                                {post.episodeNumber ? ` · Episode ${post.episodeNumber}` : ''}
                            </Link>
                        )}
                    </div>
                    <h1 className="text-3xl font-semibold tracking-tight">{post.title}</h1>
                    <p className="text-sm text-muted-foreground">
                        By {post.authorName}
                        {post.readingTimeMinutes
                            ? ` · ${post.readingTimeMinutes} min read`
                            : ''}
                    </p>
                </div>

                {showFullBody ? (
                    <>
                        {post.videoEmbedUrl && (
                            <VideoEmbed url={post.videoEmbedUrl} title={post.title} />
                        )}
                        <ContentBody content={post.body ?? ''} />
                        <div className="flex flex-wrap items-center gap-3 border-t pt-6">
                            <Button
                                type="button"
                                variant={hasReacted ? 'default' : 'outline'}
                                onClick={toggleHelpful}
                                disabled={!hasFullAccess || reacting}
                                data-test="learn-helpful-button"
                            >
                                <ThumbsUp className="mr-2 size-4" />
                                {hasReacted ? 'Helpful' : 'Mark helpful'}
                            </Button>
                            <span className="text-sm text-muted-foreground">
                                {helpfulCount === 1
                                    ? '1 person found this helpful'
                                    : `${helpfulCount} people found this helpful`}
                            </span>
                        </div>
                    </>
                ) : (
                    <div className="space-y-4 rounded-lg border border-dashed p-6">
                        {post.excerpt && (
                            <p className="text-muted-foreground">{post.excerpt}</p>
                        )}
                        {isAuthenticated ? (
                            <Button asChild>
                                <Link href="/settings/billing">Subscribe to read</Link>
                            </Button>
                        ) : (
                            <div className="flex flex-wrap gap-2">
                                <Button asChild>
                                    <Link href="/register">Create free account</Link>
                                </Button>
                                <Button variant="outline" asChild>
                                    <Link href="/login">Sign in</Link>
                                </Button>
                            </div>
                        )}
                    </div>
                )}

                {(previousEpisode || nextEpisode) && showFullBody && (
                    <div className="flex flex-wrap justify-between gap-3 border-t pt-6 text-sm">
                        {previousEpisode ? (
                            <Link
                                href={`/learn/posts/${previousEpisode.slug}`}
                                className="text-muted-foreground hover:text-foreground"
                            >
                                ← {previousEpisode.title}
                            </Link>
                        ) : (
                            <span />
                        )}
                        {nextEpisode && (
                            <Link
                                href={`/learn/posts/${nextEpisode.slug}`}
                                className="text-muted-foreground hover:text-foreground"
                            >
                                {nextEpisode.title} →
                            </Link>
                        )}
                    </div>
                )}

                <p className="border-t pt-4 text-xs text-muted-foreground">
                    Educational content only — not financial advice.
                </p>
            </article>
        </>
    );

    if (isAuthenticated) {
        return content;
    }

    return (
        <LearnMarketingShell
            isAuthenticated={isAuthenticated}
            dashboardUrl={dashboardUrl}
        >
            {content}
        </LearnMarketingShell>
    );
}
