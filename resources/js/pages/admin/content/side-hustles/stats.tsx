import { Head } from '@inertiajs/react';
import ContentEntityTabs from '@/components/admin/content-entity-tabs';
import Heading from '@/components/heading';

type Props = {
    summary: {
        total: number;
        published: number;
        draft: number;
        categoryCount: number;
    };
    byCategory: Array<{
        name: string;
        slug: string;
        hustlesCount: number;
        publishedCount: number;
    }>;
};

export default function AdminSideHustlesStats({ summary, byCategory }: Props) {
    return (
        <>
            <Head title="Admin — Side hustles stats" />
            <ContentEntityTabs entity="side-hustles" section="stats" />
            <div className="mt-4">
                <Heading
                    variant="small"
                    title="Side hustles stats"
                    description="Hustle counts by status and category."
                />
            </div>
            <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div className="rounded-lg border p-4">
                    <p className="text-sm text-muted-foreground">Total</p>
                    <p className="text-2xl font-semibold">{summary.total}</p>
                </div>
                <div className="rounded-lg border p-4">
                    <p className="text-sm text-muted-foreground">Published</p>
                    <p className="text-2xl font-semibold">{summary.published}</p>
                </div>
                <div className="rounded-lg border p-4">
                    <p className="text-sm text-muted-foreground">Draft</p>
                    <p className="text-2xl font-semibold">{summary.draft}</p>
                </div>
                <div className="rounded-lg border p-4">
                    <p className="text-sm text-muted-foreground">Categories</p>
                    <p className="text-2xl font-semibold">{summary.categoryCount}</p>
                </div>
            </div>
            <div className="mt-8">
                <h2 className="text-lg font-medium">By category</h2>
                <ul className="mt-3 space-y-3">
                    {byCategory.map((row) => (
                        <li key={row.slug} className="rounded-lg border p-4">
                            <p className="font-medium">{row.name}</p>
                            <p className="text-sm text-muted-foreground">
                                {row.publishedCount} published · {row.hustlesCount} total
                            </p>
                        </li>
                    ))}
                </ul>
            </div>
        </>
    );
}
