export function resolveNotificationActionUrl(
    actionUrl: string | null | undefined,
): string | null {
    if (!actionUrl) {
        return null;
    }

    if (actionUrl === '/dashboard') {
        return '/launch';
    }

    try {
        const url = new URL(actionUrl, window.location.origin);

        if (url.pathname === '/dashboard') {
            url.pathname = '/launch';
        }

        return url.pathname + url.search + url.hash;
    } catch {
        return actionUrl;
    }
}
