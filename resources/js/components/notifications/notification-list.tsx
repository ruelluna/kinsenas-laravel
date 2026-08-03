import { Link, router } from '@inertiajs/react';
import { formatNotificationTime } from '@/lib/format-notification-time';
import { cn } from '@/lib/utils';
import type { NotificationItem } from '@/types/notifications';

type NotificationItemRowProps = {
    notification: NotificationItem;
    compact?: boolean;
    onNavigate?: () => void;
};

export function NotificationItemRow({
    notification,
    compact = false,
    onNavigate,
}: NotificationItemRowProps) {
    const handleClick = () => {
        if (notification.isUnread) {
            router.patch(
                `/notifications/${notification.id}/read`,
                {},
                {
                    preserveScroll: true,
                    only: ['notifications'],
                },
            );
        }

        onNavigate?.();

        if (notification.actionUrl) {
            router.visit(notification.actionUrl);
        }
    };

    return (
        <button
            type="button"
            onClick={handleClick}
            className={cn(
                'flex w-full flex-col gap-1 rounded-md px-3 py-2 text-left transition-colors hover:bg-muted',
                notification.isUnread && 'bg-muted/50',
                compact ? 'text-sm' : 'text-base',
            )}
        >
            <div className="flex items-start justify-between gap-2">
                <span
                    className={cn(
                        'font-medium',
                        notification.isUnread && 'text-foreground',
                    )}
                >
                    {notification.title}
                </span>
                <span className="shrink-0 text-xs text-muted-foreground">
                    {formatNotificationTime(notification.createdAt)}
                </span>
            </div>
            <p className="text-muted-foreground">{notification.body}</p>
        </button>
    );
}

type NotificationListProps = {
    items: NotificationItem[];
    emptyMessage?: string;
    compact?: boolean;
    onNavigate?: () => void;
};

export function NotificationList({
    items,
    emptyMessage = 'No notifications yet.',
    compact = false,
    onNavigate,
}: NotificationListProps) {
    if (items.length === 0) {
        return (
            <p className="px-3 py-6 text-center text-sm text-muted-foreground">
                {emptyMessage}
            </p>
        );
    }

    return (
        <div className="flex flex-col gap-1">
            {items.map((notification) => (
                <NotificationItemRow
                    key={notification.id}
                    notification={notification}
                    compact={compact}
                    onNavigate={onNavigate}
                />
            ))}
        </div>
    );
}

export function NotificationListFooter({
    onNavigate,
}: {
    onNavigate?: () => void;
}) {
    return (
        <div className="flex items-center justify-between gap-2 border-t px-3 py-2 text-sm">
            <Link
                href="/notifications"
                className="text-primary underline-offset-4 hover:underline"
                onClick={onNavigate}
            >
                View all
            </Link>
            <Link
                href="/settings/notifications"
                className="text-muted-foreground underline-offset-4 hover:underline"
                onClick={onNavigate}
            >
                Settings
            </Link>
        </div>
    );
}
