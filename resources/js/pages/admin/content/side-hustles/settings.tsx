import { Head, Link } from '@inertiajs/react';
import { AdminEditLink } from '@/components/admin/admin-list-actions';
import ContentEntityTabs from '@/components/admin/content-entity-tabs';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';

type Category = {
    id: string;
    name: string;
    slug: string;
    status: string;
    sideHustlesCount: number;
};

type Props = {
    categories: Category[];
};

export default function AdminSideHustlesSettings({ categories }: Props) {
    return (
        <>
            <Head title="Admin — Side hustles settings" />
            <ContentEntityTabs entity="side-hustles" section="settings" />
            <div className="mt-4 flex flex-wrap items-center justify-between gap-4">
                <Heading
                    variant="small"
                    title="Side hustle categories"
                    description="Organize side hustle ideas by topic."
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
                                {category.slug} · {category.sideHustlesCount} hustles · {category.status}
                            </p>
                        </div>
                        <AdminEditLink
                            href={`/admin/content/side-hustle-categories/${category.slug}/edit`}
                        />
                    </li>
                ))}
            </ul>
        </>
    );
}
