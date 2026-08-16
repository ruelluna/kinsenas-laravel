import { Link } from '@inertiajs/react';
import ContentBody from '@/components/content/content-body';
import ContentByline from '@/components/content/content-byline';
import LearnPageHead from '@/components/learn/learn-page-head';
import LearnMarketingShell from '@/components/learn/learn-marketing-shell';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatMoney } from '@/lib/format-money';
import type { SideHustleSummary } from '@/types/learn-library';

type Props = {
    hustle: SideHustleSummary;
    showFullBody: boolean;
    hasFullAccess: boolean;
    isAuthenticated: boolean;
    openGraph?: {
        title: string;
        description: string;
        url: string;
        image: string | null;
    } | null;
};

export default function LearnSideHustleShow({
    hustle,
    showFullBody,
    hasFullAccess,
    isAuthenticated,
    openGraph = null,
}: Props) {
    const content = (
        <>
            <LearnPageHead
                title={hustle.title}
                description={hustle.excerpt}
                openGraph={openGraph}
                ogType="website"
            />
            <div className="space-y-8">
                <div className="space-y-3">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href="/learn?filter=side-hustles">← Side hustles</Link>
                    </Button>
                    <div className="flex flex-wrap gap-2">
                        {hustle.category && (
                            <Badge variant="secondary">{hustle.category.name}</Badge>
                        )}
                        <Badge variant="outline">{hustle.difficultyLabel}</Badge>
                        <Badge variant="outline">{hustle.capitalTierLabel}</Badge>
                    </div>
                    <h1 className="text-3xl font-semibold tracking-tight">{hustle.title}</h1>
                    {hustle.bylineName && <ContentByline name={hustle.bylineName} />}
                    {hustle.excerpt && (
                        <p className="text-lg text-muted-foreground">{hustle.excerpt}</p>
                    )}
                </div>

                <dl className="grid gap-4 rounded-lg border p-4 sm:grid-cols-2">
                    <div>
                        <dt className="text-sm text-muted-foreground">Startup capital</dt>
                        <dd className="font-medium">
                            {formatMoney(hustle.startupCapitalMin)} –{' '}
                            {formatMoney(hustle.startupCapitalMax)}
                        </dd>
                    </div>
                    <div>
                        <dt className="text-sm text-muted-foreground">Time per week</dt>
                        <dd className="font-medium">
                            {hustle.timeCommitmentHoursMin ?? '—'} –{' '}
                            {hustle.timeCommitmentHoursMax ?? '—'} hrs
                        </dd>
                    </div>
                    {hustle.skills.length > 0 && (
                        <div className="sm:col-span-2">
                            <dt className="text-sm text-muted-foreground">Skills</dt>
                            <dd className="mt-1 flex flex-wrap gap-2">
                                {hustle.skills.map((skill) => (
                                    <Badge key={skill} variant="outline">
                                        {skill}
                                    </Badge>
                                ))}
                            </dd>
                        </div>
                    )}
                    {hustle.equipment.length > 0 && (
                        <div className="sm:col-span-2">
                            <dt className="text-sm text-muted-foreground">Equipment</dt>
                            <dd className="mt-1 flex flex-wrap gap-2">
                                {hustle.equipment.map((item) => (
                                    <Badge key={item} variant="outline">
                                        {item}
                                    </Badge>
                                ))}
                            </dd>
                        </div>
                    )}
                </dl>

                {showFullBody && hustle.body ? (
                    <ContentBody content={hustle.body ?? ''} />
                ) : (
                    <div className="rounded-lg border border-dashed p-6 text-center">
                        <p className="text-muted-foreground">
                            {isAuthenticated
                                ? 'Subscribe to read the full hustle guide.'
                                : 'Sign in and subscribe to read the full guide.'}
                        </p>
                        {!isAuthenticated && (
                            <Button className="mt-4" asChild>
                                <Link href="/login">Sign in</Link>
                            </Button>
                        )}
                    </div>
                )}

                <p className="text-xs text-muted-foreground">
                    Educational content only — not financial or business advice.
                </p>
            </div>
        </>
    );

    return isAuthenticated ? content : <LearnMarketingShell>{content}</LearnMarketingShell>;
}
