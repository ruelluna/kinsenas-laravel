import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import {
    isWebPushSupported,
    subscribeToWebPush,
    unsubscribeFromWebPush,
} from '@/lib/web-push';
import type { NotificationPreferences } from '@/types/notifications';

type PageProps = {
    preferences: NotificationPreferences;
    pushSubscriptionCount: number;
    vapidPublicKey: string | null;
};

function PreferenceToggle({
    id,
    label,
    description,
    checked,
    onCheckedChange,
}: {
    id: string;
    label: string;
    description: string;
    checked: boolean;
    onCheckedChange: (checked: boolean) => void;
}) {
    return (
        <div className="flex items-start gap-3 rounded-lg border p-4">
            <Checkbox
                id={id}
                checked={checked}
                onCheckedChange={(value) => onCheckedChange(value === true)}
            />
            <div className="space-y-1">
                <Label htmlFor={id}>{label}</Label>
                <p className="text-sm text-muted-foreground">{description}</p>
            </div>
        </div>
    );
}

export default function NotificationsSettings({
    preferences,
    pushSubscriptionCount,
    vapidPublicKey,
}: PageProps) {
    const [pushBusy, setPushBusy] = useState(false);
    const [pushError, setPushError] = useState<string | null>(null);
    const [hasPushSubscription, setHasPushSubscription] = useState(
        pushSubscriptionCount > 0,
    );

    const pushSupported = isWebPushSupported();

    const handlePushToggle = async (enabled: boolean) => {
        if (!vapidPublicKey) {
            setPushError('Push notifications are not configured on this server.');

            return;
        }

        setPushBusy(true);
        setPushError(null);

        try {
            if (enabled) {
                await subscribeToWebPush(vapidPublicKey);
                setHasPushSubscription(true);
            } else {
                await unsubscribeFromWebPush();
                setHasPushSubscription(false);
            }
        } catch (error) {
            setPushError(
                error instanceof Error
                    ? error.message
                    : 'Unable to update push notifications.',
            );
        } finally {
            setPushBusy(false);
        }
    };

    return (
        <>
            <Head title="Notification settings" />

            <h1 className="sr-only">Notification settings</h1>

            <Form
                action="/settings/notifications"
                method="patch"
                defaults={preferences}
                options={{ preserveScroll: true }}
                className="space-y-8"
            >
                {({ data, setData, processing, errors }) => (
                    <>
                        <div className="space-y-4">
                            <Heading
                                variant="small"
                                title="Email"
                                description="Choose which updates we send to your inbox"
                            />
                            <PreferenceToggle
                                id="emailTeamInvitations"
                                label="Team invitations"
                                description="When someone invites you to join their team"
                                checked={data.emailTeamInvitations}
                                onCheckedChange={(checked) =>
                                    setData('emailTeamInvitations', checked)
                                }
                            />
                            <PreferenceToggle
                                id="emailPendingActions"
                                label="Pending actions"
                                description="When spending or transfers need confirmation"
                                checked={data.emailPendingActions}
                                onCheckedChange={(checked) =>
                                    setData('emailPendingActions', checked)
                                }
                            />
                            <PreferenceToggle
                                id="emailBillingReminders"
                                label="Billing reminders"
                                description="Trial ending and subscription updates"
                                checked={data.emailBillingReminders}
                                onCheckedChange={(checked) =>
                                    setData('emailBillingReminders', checked)
                                }
                            />
                        </div>

                        <div className="space-y-4">
                            <Heading
                                variant="small"
                                title="In-app"
                                description="Alerts shown in the notification bell"
                            />
                            <PreferenceToggle
                                id="inAppTeamInvitations"
                                label="Team invitations"
                                description="Show team invites in your notification inbox"
                                checked={data.inAppTeamInvitations}
                                onCheckedChange={(checked) =>
                                    setData('inAppTeamInvitations', checked)
                                }
                            />
                            <PreferenceToggle
                                id="inAppPendingActions"
                                label="Pending actions"
                                description="Show pending spends and transfers in your inbox"
                                checked={data.inAppPendingActions}
                                onCheckedChange={(checked) =>
                                    setData('inAppPendingActions', checked)
                                }
                            />
                            <PreferenceToggle
                                id="inAppBillingReminders"
                                label="Billing reminders"
                                description="Show trial and billing alerts in your inbox"
                                checked={data.inAppBillingReminders}
                                onCheckedChange={(checked) =>
                                    setData('inAppBillingReminders', checked)
                                }
                            />
                        </div>

                        <div className="space-y-4">
                            <Heading
                                variant="small"
                                title="Push notifications"
                                description="Browser alerts when Kinsenas is installed or open in the background"
                            />
                            {!pushSupported && (
                                <p className="text-sm text-muted-foreground">
                                    Push notifications are not supported in this
                                    browser.
                                </p>
                            )}
                            <PreferenceToggle
                                id="pushPendingActions"
                                label="Pending actions"
                                description="Push when confirmation is needed"
                                checked={data.pushPendingActions}
                                onCheckedChange={(checked) =>
                                    setData('pushPendingActions', checked)
                                }
                            />
                            <PreferenceToggle
                                id="pushBillingReminders"
                                label="Billing reminders"
                                description="Push before your trial ends"
                                checked={data.pushBillingReminders}
                                onCheckedChange={(checked) =>
                                    setData('pushBillingReminders', checked)
                                }
                            />
                            {pushSupported && (
                                <div className="flex flex-wrap items-center gap-3">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        disabled={pushBusy || !vapidPublicKey}
                                        onClick={() =>
                                            void handlePushToggle(
                                                !hasPushSubscription,
                                            )
                                        }
                                    >
                                        {hasPushSubscription
                                            ? 'Disable browser push'
                                            : 'Enable browser push'}
                                    </Button>
                                    <span className="text-sm text-muted-foreground">
                                        {hasPushSubscription
                                            ? 'This device is subscribed.'
                                            : 'Allow notifications when prompted.'}
                                    </span>
                                </div>
                            )}
                            {pushError && (
                                <p className="text-sm text-destructive">
                                    {pushError}
                                </p>
                            )}
                        </div>

                        <InputError message={errors.emailTeamInvitations} />

                        <Button type="submit" disabled={processing}>
                            Save preferences
                        </Button>
                    </>
                )}
            </Form>
        </>
    );
}

NotificationsSettings.layout = {
    breadcrumbs: [
        {
            title: 'Notification settings',
            href: '/settings/notifications',
        },
    ],
};
