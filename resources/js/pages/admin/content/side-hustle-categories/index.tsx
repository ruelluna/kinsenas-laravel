import { Head, Link } from '@inertiajs/react';
import ContentAdminTabs from '@/components/admin/content-admin-tabs';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import type { SideHustleCategoryAdmin } from '@/types/learn-library';

type Props = {
    categories: SideHustleCategoryAdmin[];
};

export default function AdminSideHustleCategoriesIndex({ categories }: Props) {
    return (
        <>
            <Head title="Admin — Hustle categories" />
            <ContentAdminTabs active="hustle-categories" />
            <div className="mt-4 flex flex-wrap items-center justify-between gap-4">
                <Heading
                    variant="small"
                    title="Hustle categories"
                    description="Organize side hustles into browsable categories."
                />
                <Button asChild>
                    <Link href="/admin/content/side-hustle-categories/create">New category</Link>
                </Button>
            </div>
            <ul className="mt-6 space-y-3">
                {categories.map((category) => (
                    <li
                        key={category.id}
                        className="flex flex-wrap items-center justify-between gap-4 rounded-lg border p-4"
                    >
                        <div>
                            <p className="font-medium">{category.name}</p>
                            <p className="text-sm text-muted-foreground">
                                {category.slug} · {category.sideHustlesCount} hustles ·{' '}
                                {category.status}
                            </p>
                        </div>
                        <Button variant="outline" asChild>
                            <Link href={`/admin/content/side-hustle-categories/${category.slug}/edit`}>
                                Edit
                            </Link>
                        </Button>
                    </li>
                ))}
            </ul>
        </>
    );
}
