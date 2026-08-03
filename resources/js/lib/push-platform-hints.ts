export type PushPlatform = 'android' | 'ios' | 'desktop' | 'unknown';

export function detectPushPlatform(): PushPlatform {
    if (typeof navigator === 'undefined') {
        return 'unknown';
    }

    const ua = navigator.userAgent;

    if (/Android/i.test(ua)) {
        return 'android';
    }

    if (/iPad|iPhone|iPod/i.test(ua)) {
        return 'ios';
    }

    if (/Mobi|Mobile/i.test(ua)) {
        return 'unknown';
    }

    return 'desktop';
}

export function pushSetupHints(platform: PushPlatform): string[] {
    if (platform === 'android') {
        return [
            'Use Chrome on Android (recommended).',
            'Tap Enable browser push, then choose Allow when Chrome asks.',
            'If you miss the prompt, open Android Settings → Apps → Chrome → Notifications and allow them.',
            'Add Kinsenas to your Home screen for more reliable background alerts.',
            'After enabling push, send a test notification, then leave the app or lock your phone — the banner appears in the Android notification shade, not inside Kinsenas.',
        ];
    }

    if (platform === 'ios') {
        return [
            'On iPhone, Web Push works when Kinsenas is added to the Home screen (iOS 16.4+).',
            'Open Kinsenas from the home screen icon, enable browser push, then Allow notifications.',
            'Safari tabs alone may only show in-app bell alerts.',
        ];
    }

    return [
        'Enable browser push and allow notifications when your browser prompts you.',
        'OS banners appear when the tab is in the background or closed; the bell inbox is separate.',
    ];
}
