import { Head, Link } from '@inertiajs/react';
import LearnMarketingShell from '@/components/learn/learn-marketing-shell';
import { Button } from '@/components/ui/button';
import type { PodcastShowSummary } from '@/types/learn-library';

type Props = {
    hasFullAccess: boolean;
    isAuthenticated: boolean;
    shows: PodcastShowSummary[];
};

export default function LearnPodcastsIndex({ hasFullAccess, isAuthenticated, shows }: Props) {
    const content = (
        <>
            <Head title="Podcasts" />
            <div className="space-y-8">
                <div>
                    <h1 className="text-3xl font-semibold tracking-tight">Podcasts</h1>
                    <p className="mt-2 text-muted-foreground">
                        Listen to Kinsenas episodes on money habits, side hustles, and payday planning.
                    </p>
                </div>

                <div className="grid gap-4 sm:grid-cols-2">
                    {shows.map((show) => (
                        <Link
                            key={show.id}
                            href={`/learn/podcasts/${show.slug}`}
                            className="rounded-lg border p-4 transition hover:border-primary/40"
                        >
                            <h2 className="text-lg font-medium">{show.title}</h2>
                            {show.description && (
                                <p className="mt-2 text-sm text-muted-foreground">{show.description}</p>
                            )}
                        </Link>
                    ))}
                </div>

                {!hasFullAccess && (
                    <p className="text-sm text-muted-foreground">
                        Some episodes may require a subscription for full show notes.
                    </p>
                )}
            </div>
        </>
    );

    return isAuthenticated ? content : <LearnMarketingShell>{content}</LearnMarketingShell>;
}
