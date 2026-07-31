import { Form, Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { AdminPlatformUser } from '@/types/billing';

type PaginatedUsers = {
    data: AdminPlatformUser[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

type Props = {
    users: PaginatedUsers;
    filters: {
        search?: string | null;
        admin?: string | null;
    };
    currentUserId: number;
    platformAdminCount: number;
};

export default function AdminPlatformUsersIndex({
    users,
    filters,
    currentUserId,
    platformAdminCount,
}: Props) {
    return (
        <>
            <Head title="Admin — Platform users" />
            <Heading
                variant="small"
                title="Platform admins"
                description={`${platformAdminCount} platform admin(s). Grant or revoke access.`}
            />

            <Form method="get" action="/admin/platform-users" className="mt-6 flex flex-wrap gap-3">
                <div className="grid gap-2">
                    <Label htmlFor="search">Search</Label>
                    <Input id="search" name="search" defaultValue={filters.search ?? ''} placeholder="Name or email" />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="admin">Admin filter</Label>
                    <select
                        id="admin"
                        name="admin"
                        defaultValue={filters.admin ?? ''}
                        className="border-input h-9 rounded-md border px-3 text-sm"
                    >
                        <option value="">All users</option>
                        <option value="yes">Platform admins only</option>
                        <option value="no">Non-admins only</option>
                    </select>
                </div>
                <div className="flex items-end">
                    <Button type="submit" variant="outline">
                        Filter
                    </Button>
                </div>
            </Form>

            <div className="mt-6 space-y-3">
                {users.data.map((user) => {
                    const isSelf = user.id === currentUserId;
                    const isLastAdmin = user.isPlatformAdmin && platformAdminCount <= 1;

                    return (
                        <div key={user.id} className="rounded-lg border p-4 text-sm">
                            <div className="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p className="font-medium">
                                        {user.name}
                                        {isSelf && (
                                            <span className="ml-2 text-muted-foreground">(you)</span>
                                        )}
                                    </p>
                                    <p className="text-muted-foreground">{user.email}</p>
                                    <p className="mt-1 text-muted-foreground">
                                        Subscription: {user.subscriptionStatusLabel ?? 'None'}
                                    </p>
                                </div>
                                <Form
                                    action={`/admin/platform-users/${user.id}`}
                                    method="post"
                                    className="flex items-center gap-2"
                                >
                                    <input type="hidden" name="_method" value="patch" />
                                    <input
                                        type="hidden"
                                        name="is_platform_admin"
                                        value={user.isPlatformAdmin ? '0' : '1'}
                                    />
                                    <Button
                                        type="submit"
                                        size="sm"
                                        variant={user.isPlatformAdmin ? 'outline' : 'default'}
                                        disabled={isSelf || isLastAdmin}
                                    >
                                        {user.isPlatformAdmin ? 'Revoke admin' : 'Grant admin'}
                                    </Button>
                                </Form>
                            </div>
                            {(isSelf || isLastAdmin) && user.isPlatformAdmin && (
                                <p className="mt-2 text-xs text-muted-foreground">
                                    {isSelf
                                        ? 'You cannot revoke your own platform admin access.'
                                        : 'At least one platform admin must remain.'}
                                </p>
                            )}
                        </div>
                    );
                })}
            </div>
        </>
    );
}
