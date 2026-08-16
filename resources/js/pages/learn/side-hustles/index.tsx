import { Head, Link, router } from '@inertiajs/react';
import LearnMarketingShell from '@/components/learn/learn-marketing-shell';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatMoney } from '@/lib/format-money';
import type {
    PaginatedLibrary,
    SideHustleCategorySummary,
    SideHustleSummary,
} from '@/types/learn-library';

type Props = {
    hasFullAccess: boolean;
    isAuthenticated: boolean;
    categories: SideHustleCategorySummary[];
    activeCategory: string | null;
    hustles: PaginatedLibrary<SideHustleSummary>;
};

export default function LearnSideHustlesIndex({
    hasFullAccess,
    isAuthenticated,
    categories,
    activeCategory,
    hustles,
}: Props) {
    const content = (
        <>
            <Head title="Side hustles" />
            <div className="space-y-8">
                <div>
                    <h1 className="text-3xl font-semibold tracking-tight">Side hustle library</h1>
                    <p className="mt-2 text-muted-foreground">
                        Ideas you can try — from street food to online work — with startup capital and
                        skill guides.
                    </p>
                </div>

                <div className="flex flex-wrap gap-2">
                    <Button
                        variant={activeCategory ? 'outline' : 'default'}
                        size="sm"
                        onClick={() => router.get('/learn/side-hustles', {}, { preserveState: true })}
                    >
                        All
                    </Button>
                    {categories.map((category) => (
                        <Button
                            key={category.id}
                            variant={activeCategory === category.slug ? 'default' : 'outline'}
                            size="sm"
                            onClick={() =>
                                router.get(
                                    '/learn/side-hustles',
                                    { category: category.slug },
                                    { preserveState: true },
                                )
                            }
                        >
                            {category.name}
                        </Button>
                    ))}
                </div>

                <div className="grid gap-4 sm:grid-cols-2">
                    {hustles.data.map((hustle) => (
                        <Link
                            key={hustle.id}
                            href={`/learn/side-hustles/${hustle.slug}`}
                            className="rounded-lg border p-4 transition hover:border-primary/40"
                        >
                            <div className="flex flex-wrap gap-2">
                                {hustle.category && (
                                    <Badge variant="secondary">{hustle.category.name}</Badge>
                                )}
                                <Badge variant="outline">{hustle.difficultyLabel}</Badge>
                                <Badge variant="outline">{hustle.capitalTierLabel}</Badge>
                            </div>
                            <h2 className="mt-3 text-lg font-medium">{hustle.title}</h2>
                            {hustle.excerpt && (
                                <p className="mt-2 text-sm text-muted-foreground">{hustle.excerpt}</p>
                            )}
                            {(hustle.startupCapitalMin || hustle.startupCapitalMax) && (
                                <p className="mt-3 text-sm">
                                    Startup: {formatMoney(hustle.startupCapitalMin)} –{' '}
                                    {formatMoney(hustle.startupCapitalMax)}
                                </p>
                            )}
                        </Link>
                    ))}
                </div>

                {!hasFullAccess && (
                    <p className="text-sm text-muted-foreground">
                        {isAuthenticated
                            ? 'Subscribe to read full hustle guides.'
                            : 'Sign in and subscribe to read full hustle guides.'}
                    </p>
                )}
            </div>
        </>
    );

    return isAuthenticated ? content : <LearnMarketingShell>{content}</LearnMarketingShell>;
}
