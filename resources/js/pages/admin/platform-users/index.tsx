import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import DeletePlatformUserModal from '@/components/admin/delete-platform-user-modal';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type {
    AdminPlatformUser,
    PlatformRoleOption,
} from '@/types/billing';

type PaginatedUsers = {
    data: AdminPlatformUser[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

type Props = {
    users: PaginatedUsers;
    filters: {
        search?: string | null;
        role?: string | null;
    };
    currentUserId: number;
    platformAdminCount: number;
    roleOptions: PlatformRoleOption[];
};

export default function AdminPlatformUsersIndex({
    users,
    filters,
    currentUserId,
    platformAdminCount,
    roleOptions,
}: Props) {
    const [userToDelete, setUserToDelete] = useState<AdminPlatformUser | null>(
        null,
    );

    return (
        <>
            <Head title="Admin — Users" />
            <Heading
                variant="small"
                title="Users"
                description={`Manage platform roles and remove user accounts. ${platformAdminCount} platform admin(s).`}
            />

            <Form
                method="get"
                action="/admin/platform-users"
                className="mt-6 flex flex-wrap gap-3"
            >
                <div className="grid gap-2">
                    <Label htmlFor="search">Search</Label>
                    <Input
                        id="search"
                        name="search"
                        defaultValue={filters.search ?? ''}
                        placeholder="Name or email"
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="role">Role filter</Label>
                    <select
                        id="role"
                        name="role"
                        defaultValue={filters.role ?? ''}
                        className="h-9 rounded-md border border-input px-3 text-sm"
                    >
                        <option value="">All roles</option>
                        {roleOptions.map((option) => (
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
                {users.data.map((user) => {
                    const isSelf = user.id === currentUserId;
                    const isLastAdmin =
                        user.isPlatformAdmin && platformAdminCount <= 1;
                    const canDelete = user.deleteBlockReason === null;

                    return (
                        <div
                            key={user.id}
                            className="rounded-lg border p-4 text-sm"
                        >
                            <div className="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p className="font-medium">
                                        {user.name}
                                        {isSelf && (
                                            <span className="ml-2 text-muted-foreground">
                                                (you)
                                            </span>
                                        )}
                                    </p>
                                    <p className="text-muted-foreground">
                                        {user.email}
                                    </p>
                                    <p className="mt-1 text-muted-foreground">
                                        Role: {user.roleLabel}
                                    </p>
                                    <p className="text-muted-foreground">
                                        Subscription:{' '}
                                        {user.subscriptionStatusLabel ?? 'None'}
                                    </p>
                                </div>
                                <div className="flex flex-wrap items-center gap-2">
                                    <Form
                                        action={`/admin/platform-users/${user.id}`}
                                        method="post"
                                        className="flex items-center gap-2"
                                    >
                                        <input
                                            type="hidden"
                                            name="_method"
                                            value="patch"
                                        />
                                        <select
                                            name="role"
                                            defaultValue={user.role}
                                            disabled={isSelf || isLastAdmin}
                                            className="h-8 rounded-md border border-input px-2 text-sm"
                                            onChange={(event) => {
                                                event.currentTarget.form?.requestSubmit();
                                            }}
                                        >
                                            {roleOptions.map((option) => (
                                                <option
                                                    key={option.value}
                                                    value={option.value}
                                                >
                                                    {option.label}
                                                </option>
                                            ))}
                                        </select>
                                    </Form>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="destructive"
                                        disabled={!canDelete}
                                        data-test="delete-user-button"
                                        onClick={() => setUserToDelete(user)}
                                    >
                                        Remove user
                                    </Button>
                                </div>
                            </div>
                            {(isSelf || isLastAdmin) &&
                                user.isPlatformAdmin && (
                                    <p className="mt-2 text-xs text-muted-foreground">
                                        {isSelf
                                            ? 'You cannot change your own platform role.'
                                            : 'At least one platform admin must remain.'}
                                    </p>
                                )}
                            {user.deleteBlockReason &&
                                !(
                                    (isSelf || isLastAdmin) &&
                                    user.isPlatformAdmin
                                ) && (
                                    <p className="mt-2 text-xs text-muted-foreground">
                                        {user.deleteBlockReason}
                                    </p>
                                )}
                        </div>
                    );
                })}
            </div>

            {userToDelete && (
                <DeletePlatformUserModal
                    user={userToDelete}
                    open={userToDelete !== null}
                    onOpenChange={(open) => {
                        if (!open) {
                            setUserToDelete(null);
                        }
                    }}
                />
            )}
        </>
    );
}
