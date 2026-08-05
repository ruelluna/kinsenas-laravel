export type NotificationItem = {
    id: string;
    kind: string;
    title: string;
    body: string;
    actionUrl: string | null;
    meta: Record<string, unknown>;
    readAt: string | null;
    createdAt: string | null;
    isUnread: boolean;
};

export type NotificationInboxPage = {
    data: NotificationItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};

export type NotificationInboxResponse = {
    inbox: NotificationInboxPage;
    unreadCount: number;
};

export type NotificationUnreadCountResponse = {
    unreadCount: number;
};
