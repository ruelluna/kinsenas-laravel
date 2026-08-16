import { router, usePage } from '@inertiajs/react';
import { Bell } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';
import {
    NotificationList,
    NotificationListFooter,
} from '@/components/notifications/notification-list';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { SharedData } from '@/types';
import type { NotificationItem } from '@/types/notifications';

export function NotificationBell() {
    const { notifications } = usePage<SharedData>().props;
    const unreadCount = notifications?.unreadCount ?? 0;
    const [open, setOpen] = useState(false);
    const [items, setItems] = useState<NotificationItem[]>([]);
    const [loading, setLoading] = useState(false);

    const loadRecent = useCallback(async () => {
        setLoading(true);

        try {
            const response = await fetch('/notifications/recent', {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const data = (await response.json()) as {
                items: NotificationItem[];
            };

            setItems(data.items);
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        if (open) {
            void loadRecent();
        }
    }, [open, loadRecent, unreadCount]);

    const markAllRead = () => {
        router.post(
            '/notifications/read-all',
            {},
            {
                preserveScroll: true,
                only: ['notifications'],
                onSuccess: () => {
                    void loadRecent();
                },
            },
        );
    };

    return (
        <DropdownMenu open={open} onOpenChange={setOpen}>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="ghost"
                    size="icon"
                    className="relative size-9"
                    aria-label="Notifications"
                >
                    <Bell className="size-4" />
                    {unreadCount > 0 && (
                        <span className="absolute -top-0.5 -right-0.5 flex min-h-4 min-w-4 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-semibold text-primary-foreground">
                            {unreadCount > 9 ? '9+' : unreadCount}
                        </span>
                    )}
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-80 p-0">
                <div className="flex items-center justify-between px-3 py-2">
                    <DropdownMenuLabel className="p-0">
                        Notifications
                    </DropdownMenuLabel>
                    {unreadCount > 0 && (
                        <button
                            type="button"
                            onClick={markAllRead}
                            className="text-xs text-primary underline-offset-4 hover:underline"
                        >
                            Mark all read
                        </button>
                    )}
                </div>
                <DropdownMenuSeparator className="m-0" />
                <div className="max-h-80 overflow-y-auto py-1">
                    {loading ? (
                        <p className="px-3 py-6 text-center text-sm text-muted-foreground">
                            Loading…
                        </p>
                    ) : (
                        <NotificationList
                            items={items}
                            compact
                            onNavigate={() => setOpen(false)}
                        />
                    )}
                </div>
                <NotificationListFooter onNavigate={() => setOpen(false)} />
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
