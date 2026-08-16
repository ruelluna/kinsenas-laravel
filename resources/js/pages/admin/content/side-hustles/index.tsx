import { Head, Link } from '@inertiajs/react';
import ContentAdminTabs from '@/components/admin/content-admin-tabs';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import type { PaginatedLibrary, SideHustleAdmin } from '@/types/learn-library';

type Props = {
    hustles: PaginatedLibrary<SideHustleAdmin>;
};

export default function AdminSideHustlesIndex({ hustles }: Props) {
    return (
        <>
            <Head title="Admin — Side hustles" />
            <ContentAdminTabs active="side-hustles" />
            <div className="mt-4 flex flex-wrap items-center justify-between gap-4">
                <Heading
                    variant="small"
                    title="Side hustles"
                    description="Structured hustle profiles with capital, skills, and guides."
                />
                <Button asChild>
                    <Link href="/admin/content/side-hustles/create">New side hustle</Link>
                </Button>
            </div>
            <ul className="mt-6 space-y-3">
                {hustles.data.map((hustle) => (
                    <li
                        key={hustle.id}
                        className="flex flex-wrap items-center justify-between gap-4 rounded-lg border p-4"
                    >
                        <div>
                            <p className="font-medium">{hustle.title}</p>
                            <p className="text-sm text-muted-foreground">
                                {hustle.slug} · {hustle.category?.name ?? 'Uncategorized'} ·{' '}
                                {hustle.capitalTierLabel} · {hustle.status}
                            </p>
                        </div>
                        <Button variant="outline" asChild>
                            <Link href={`/admin/content/side-hustles/${hustle.slug}/edit`}>
                                Edit
                            </Link>
                        </Button>
                    </li>
                ))}
            </ul>
        </>
    );
}
