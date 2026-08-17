import { Head, Link } from '@inertiajs/react';
import ContentAdminTabs from '@/components/admin/content-admin-tabs';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';

type Category = {
    id: string;
    name: string;
    slug: string;
    status: string;
    postsCount: number;
};

type Props = {
    categories: Category[];
};

export default function AdminCommunityCategoriesIndex({ categories }: Props) {
    return (
        <>
            <Head title="Admin — Community categories" />
            <ContentAdminTabs active="community-categories" />
            <div className="mt-4 flex flex-wrap items-center justify-between gap-4">
                <Heading
                    variant="small"
                    title="Community categories"
                    description="Organize member stories into browsable categories."
                />
                <Button asChild>
                    <Link href="/admin/content/community-categories/create">New category</Link>
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
                                {category.slug} · {category.postsCount} posts · {category.status}
                            </p>
                        </div>
                        <Button variant="outline" asChild>
                            <Link href={`/admin/content/community-categories/${category.slug}/edit`}>
                                Edit
                            </Link>
                        </Button>
                    </li>
                ))}
            </ul>
        </>
    );
}
