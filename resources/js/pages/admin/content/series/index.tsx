import { Head, Link } from '@inertiajs/react';
import { AdminEditLink } from '@/components/admin/admin-list-actions';
import ContentEntityTabs from '@/components/admin/content-entity-tabs';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import type { ContentSeriesAdmin } from '@/types/content';

type Props = {
    series: ContentSeriesAdmin[];
};

export default function AdminContentSeriesIndex({ series }: Props) {
    return (
        <>
            <Head title="Admin — Content series" />
            <ContentEntityTabs entity="series" section="list" />
            <div className="mt-4 flex flex-wrap items-center justify-between gap-4">
                <Heading
                    variant="small"
                    title="Content series"
                    description="Group episodes into sequential learning paths."
                />
                <Button asChild>
                    <Link href="/admin/content/series/create">New series</Link>
                </Button>
            </div>
            <ul className="mt-6 space-y-3">
                {series.map((item) => (
                    <li
                        key={item.id}
                        className="flex flex-wrap items-center justify-between gap-4 rounded-lg border p-4"
                    >
                        <div>
                            <p className="font-medium">{item.title}</p>
                            <p className="text-sm text-muted-foreground">
                                {item.slug} · {item.postsCount} posts · {item.status}
                            </p>
                        </div>
                        <AdminEditLink href={`/admin/content/series/${item.slug}/edit`} />
                    </li>
                ))}
            </ul>
        </>
    );
}
