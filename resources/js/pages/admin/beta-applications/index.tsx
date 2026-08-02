import { Form, Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import type { AdminBetaApplication, FilterOption } from '@/types/billing';

type Props = {
    applications: {
        data: AdminBetaApplication[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters: {
        status: string;
        search: string | null;
    };
    statusOptions: FilterOption[];
};

export default function AdminBetaApplicationsIndex({
    applications,
    filters,
    statusOptions,
}: Props) {
    return (
        <>
            <Head title="Admin — Beta applications" />
            <Heading
                variant="small"
                title="Beta applications"
                description="Review open beta sign-ups and approve access."
            />

            <Form
                method="get"
                action="/admin/beta-applications"
                className="mt-6 flex flex-wrap items-end gap-3"
            >
                <div className="grid gap-2">
                    <Label htmlFor="status">Status</Label>
                    <select
                        id="status"
                        name="status"
                        defaultValue={filters.status}
                        className="h-9 rounded-md border border-input px-3 text-sm"
                    >
                        {statusOptions.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="search">Search</Label>
                    <input
                        id="search"
                        name="search"
                        defaultValue={filters.search ?? ''}
                        placeholder="Name or email"
                        className="h-9 rounded-md border border-input px-3 text-sm"
                    />
                </div>
                <Button type="submit" variant="secondary" size="sm">
                    Filter
                </Button>
            </Form>

            <div className="mt-6 space-y-4">
                {applications.data.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        No applications match this filter.
                    </p>
                ) : (
                    applications.data.map((application) => (
                        <article
                            key={application.id}
                            className="rounded-lg border p-4 text-sm"
                        >
                            <div className="flex flex-wrap items-start justify-between gap-4">
                                <div className="space-y-1">
                                    <p className="font-medium">
                                        {application.name}
                                    </p>
                                    <p className="text-muted-foreground">
                                        {application.email}
                                    </p>
                                    <p className="text-muted-foreground">
                                        {application.statusLabel}
                                        {application.emailVerified
                                            ? ' · Email verified'
                                            : ' · Email not verified'}
                                        {' · '}
                                        {application.sourceLabel}
                                    </p>
                                    {application.appliedAt && (
                                        <p className="text-muted-foreground">
                                            Applied{' '}
                                            {new Date(
                                                application.appliedAt,
                                            ).toLocaleString()}
                                        </p>
                                    )}
                                </div>

                                {application.status === 'pending' && (
                                    <div className="flex flex-wrap gap-2">
                                        <Form
                                            action={`/admin/beta-applications/${application.id}/approve`}
                                            method="post"
                                        >
                                            <Button type="submit" size="sm">
                                                Approve
                                            </Button>
                                        </Form>
                                        <Form
                                            action={`/admin/beta-applications/${application.id}/reject`}
                                            method="post"
                                        >
                                            <Button
                                                type="submit"
                                                size="sm"
                                                variant="outline"
                                            >
                                                Reject
                                            </Button>
                                        </Form>
                                    </div>
                                )}
                            </div>
                        </article>
                    ))
                )}
            </div>
        </>
    );
}
