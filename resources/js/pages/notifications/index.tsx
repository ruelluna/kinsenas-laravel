import { Head, Link, router } from '@inertiajs/react';
import { NotificationList } from '@/components/notifications/notification-list';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import type { NotificationItem } from '@/types/notifications';

type PaginatedNotifications = {
    data: NotificationItem[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

export default function NotificationsIndex({
    inbox,
}: {
    inbox: PaginatedNotifications;
}) {
    const markAllRead = () => {
        router.post('/notifications/read-all', {}, { preserveScroll: true });
    };

    return (
        <>
            <Head title="Notifications" />

            <div className="space-y-6">
                <div className="flex items-center justify-between gap-3">
                    <Heading
                        title="Notifications"
                        description="Your recent alerts and reminders"
                    />
                    <Button variant="outline" size="sm" onClick={markAllRead}>
                        Mark all read
                    </Button>
                </div>

                <div className="rounded-lg border">
                    <NotificationList items={inbox.data} />
                </div>

                {inbox.links.length > 3 && (
                    <div className="flex flex-wrap gap-2">
                        {inbox.links.map((link) =>
                            link.url ? (
                                <Link
                                    key={link.label}
                                    href={link.url}
                                    className={
                                        link.active
                                            ? 'text-sm font-medium text-primary'
                                            : 'text-sm text-muted-foreground hover:text-foreground'
                                    }
                                    preserveScroll
                                >
                                    <span
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                </Link>
                            ) : (
                                <span
                                    key={link.label}
                                    className="text-sm text-muted-foreground"
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
                                />
                            ),
                        )}
                    </div>
                )}
            </div>
        </>
    );
}

NotificationsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Notifications',
            href: '/notifications',
        },
    ],
};
