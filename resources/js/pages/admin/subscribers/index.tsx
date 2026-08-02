import { Form, Head, Link } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { AdminSubscriber, FilterOption } from '@/types/billing';

type PaginatedSubscribers = {
    data: AdminSubscriber[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

type Props = {
    subscribers: PaginatedSubscribers;
    filters: {
        status?: string | null;
        search?: string | null;
    };
    statusOptions: FilterOption[];
};

export default function AdminSubscribersIndex({
    subscribers,
    filters,
    statusOptions,
}: Props) {
    return (
        <>
            <Head title="Admin — Subscribers" />
            <Heading
                variant="small"
                title="Subscribers"
                description="Search and manage team subscription lifecycle."
            />

            <Form
                method="get"
                action="/admin/subscribers"
                className="mt-6 flex flex-wrap gap-3"
            >
                <div className="grid gap-2">
                    <Label htmlFor="search">Search</Label>
                    <Input
                        id="search"
                        name="search"
                        defaultValue={filters.search ?? ''}
                        placeholder="Team name or slug"
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="status">Status</Label>
                    <select
                        id="status"
                        name="status"
                        defaultValue={filters.status ?? ''}
                        className="h-9 rounded-md border border-input px-3 text-sm"
                    >
                        {statusOptions.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                </div>
                <div className="flex items-end">
                    <Button type="submit" variant="outline">
                        Filter
                    </Button>
                </div>
            </Form>

            <div className="mt-6 space-y-3">
                {subscribers.data.map((subscriber) => (
                    <div
                        key={subscriber.id}
                        className="rounded-lg border p-4 text-sm"
                    >
                        <div className="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p className="font-medium">{subscriber.name}</p>
                                <p className="text-muted-foreground">
                                    {subscriber.ownerName ?? '—'}
                                    {subscriber.ownerEmail
                                        ? ` · ${subscriber.ownerEmail}`
                                        : ''}
                                </p>
                                <p className="mt-1 text-muted-foreground">
                                    {subscriber.subscription?.statusLabel ??
                                        'No subscription'}
                                    {subscriber.subscription?.planName
                                        ? ` · ${subscriber.subscription.planName}`
                                        : ''}
                                    {subscriber.subscription?.hasAccess
                                        ? ' · Access granted'
                                        : ' · No access'}
                                    {subscriber.isPersonal
                                        ? ' · Personal workspace'
                                        : ''}
                                </p>
                            </div>
                            <Button variant="outline" size="sm" asChild>
                                <Link
                                    href={`/admin/subscribers/${subscriber.slug}`}
                                >
                                    Manage
                                </Link>
                            </Button>
                        </div>
                    </div>
                ))}
            </div>

            {subscribers.links.length > 3 && (
                <div className="mt-6 flex flex-wrap gap-2">
                    {subscribers.links.map((link) => (
                        <Button
                            key={link.label}
                            variant={link.active ? 'default' : 'outline'}
                            size="sm"
                            disabled={!link.url}
                            asChild={!!link.url}
                        >
                            {link.url ? (
                                <Link
                                    href={link.url}
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
                                />
                            ) : (
                                <span
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
                                />
                            )}
                        </Button>
                    ))}
                </div>
            )}
        </>
    );
}
