import { Form, Head, Link } from '@inertiajs/react';
import { AdminEditLink } from '@/components/admin/admin-list-actions';
import ContentEntityTabs from '@/components/admin/content-entity-tabs';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { PaginatedLibrary } from '@/types/learn-library';

type Category = {
    id: string;
    name: string;
    slug: string;
    status: string;
    postsCount: number;
};

type PendingPost = {
    id: string;
    title: string;
    slug: string;
    authorName: string | null;
    categoryNames: string | null;
};

type Report = {
    id: string;
    reasonLabel: string;
    details: string | null;
    postTitle: string | null;
    postSlug: string | null;
    reporterName: string | null;
};

type Props = {
    categories: Category[];
    pendingPosts: PendingPost[];
    reports: Report[];
};

export default function AdminCommunitySettings({ categories, pendingPosts, reports }: Props) {
    return (
        <>
            <Head title="Admin — Community settings" />
            <ContentEntityTabs entity="community" section="settings" />
            <div className="mt-4">
                <Heading
                    variant="small"
                    title="Community settings"
                    description="Categories, moderation queue, and member reports."
                />
            </div>

            <section className="mt-8" id="categories">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <h2 className="text-lg font-medium">Categories</h2>
                    <Button asChild size="sm">
                        <Link href="/admin/content/community-categories/create">New category</Link>
                    </Button>
                </div>
                <ul className="mt-4 space-y-3">
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
                            <AdminEditLink
                                href={`/admin/content/community-categories/${category.slug}/edit`}
                            />
                        </li>
                    ))}
                </ul>
            </section>

            <section className="mt-10" id="moderation">
                <h2 className="text-lg font-medium">Moderation queue</h2>
                <div className="mt-4 space-y-4">
                    {pendingPosts.length === 0 && (
                        <p className="text-sm text-muted-foreground">No posts awaiting review.</p>
                    )}
                    {pendingPosts.map((post) => (
                        <div key={post.id} className="rounded-lg border p-4">
                            <p className="font-medium">{post.title}</p>
                            <p className="text-sm text-muted-foreground">
                                {post.authorName} · {post.categoryNames}
                            </p>
                            <div className="mt-3 flex flex-wrap gap-2">
                                <Form
                                    action={`/admin/content/community-posts/${post.slug}/approve`}
                                    method="post"
                                >
                                    <Button type="submit" size="sm" data-test="community-approve-button">
                                        Approve
                                    </Button>
                                </Form>
                                <Form
                                    action={`/admin/content/community-posts/${post.slug}/reject`}
                                    method="post"
                                    className="flex flex-wrap items-end gap-2"
                                >
                                    <div className="grid gap-1">
                                        <Label htmlFor={`reason-${post.id}`}>Rejection reason</Label>
                                        <Input
                                            id={`reason-${post.id}`}
                                            name="rejection_reason"
                                            required
                                        />
                                    </div>
                                    <Button type="submit" size="sm" variant="destructive">
                                        Reject
                                    </Button>
                                </Form>
                            </div>
                        </div>
                    ))}
                </div>
            </section>

            <section className="mt-10" id="reports">
                <h2 className="text-lg font-medium">Open reports</h2>
                <div className="mt-4 space-y-4">
                    {reports.length === 0 && (
                        <p className="text-sm text-muted-foreground">No open reports.</p>
                    )}
                    {reports.map((report) => (
                        <div key={report.id} className="rounded-lg border p-4">
                            <p className="font-medium">{report.postTitle}</p>
                            <p className="text-sm text-muted-foreground">
                                {report.reasonLabel} · reported by {report.reporterName}
                            </p>
                            {report.details && <p className="mt-2 text-sm">{report.details}</p>}
                            <div className="mt-3 flex gap-2">
                                <Form
                                    action={`/admin/content/community-reports/${report.id}/dismiss`}
                                    method="post"
                                >
                                    <Button type="submit" size="sm" variant="outline">
                                        Dismiss
                                    </Button>
                                </Form>
                                <Form
                                    action={`/admin/content/community-reports/${report.id}/resolve`}
                                    method="post"
                                >
                                    <Button type="submit" size="sm">
                                        Resolve
                                    </Button>
                                </Form>
                            </div>
                        </div>
                    ))}
                </div>
            </section>
        </>
    );
}
