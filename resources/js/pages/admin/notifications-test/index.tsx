import { Form, Head, usePage } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type ServerStatus = {
    vapidConfigured: boolean;
    vapidSubjectConfigured: boolean;
};

type UserStatus = {
    pushEnabled: boolean;
    subscriptionCount: number;
    contentEncodings: string[];
};

type Props = {
    subscriberCount: number;
    serverStatus: ServerStatus;
    userStatus: UserStatus | null;
    checklist: string[];
    errors?: {
        title?: string;
        body?: string;
        actionUrl?: string;
        target?: string;
        targetEmail?: string;
    };
};

export default function AdminNotificationsTestIndex({
    subscriberCount = 0,
    serverStatus,
    userStatus,
    checklist,
    errors = {},
}: Props) {
    const flash = usePage<{ flash?: { success?: string } }>().props.flash;

    return (
        <>
            <Head title="Admin — Push test" />
            <Heading
                variant="small"
                title="Push test"
                description="Send a test Web Push to yourself, a user email, or all subscribers."
            />

            {flash?.success && (
                <p className="mt-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-900 dark:border-green-900 dark:bg-green-950 dark:text-green-100">
                    {flash.success}
                </p>
            )}

            <div className="mt-4 space-y-4 rounded-lg border p-4 text-sm">
                <p>
                    Active push subscribers (all users):{' '}
                    <strong>{subscriberCount}</strong>
                </p>
                <p>
                    VAPID configured:{' '}
                    <strong>
                        {serverStatus.vapidConfigured ? 'Yes' : 'No'}
                    </strong>
                </p>
                {userStatus && (
                    <>
                        <p>
                            Your account — push enabled:{' '}
                            <strong>
                                {userStatus.pushEnabled ? 'Yes' : 'No'}
                            </strong>
                            , subscriptions:{' '}
                            <strong>{userStatus.subscriptionCount}</strong>
                        </p>
                        {userStatus.contentEncodings.length > 0 && (
                            <p>
                                Encoding(s):{' '}
                                {userStatus.contentEncodings.join(', ')}
                            </p>
                        )}
                    </>
                )}
                {checklist.length > 0 && (
                    <ul className="list-disc space-y-1 pl-5 text-muted-foreground">
                        {checklist.map((item) => (
                            <li key={item}>{item}</li>
                        ))}
                    </ul>
                )}
            </div>

            <Form
                method="post"
                action="/admin/notifications-test"
                className="mt-6 max-w-xl space-y-4"
            >
                <div className="grid gap-2">
                    <Label htmlFor="title">Title</Label>
                    <Input
                        id="title"
                        name="title"
                        defaultValue="Kinsenas test push"
                        required
                    />
                    <InputError message={errors.title} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="body">Body</Label>
                    <Input
                        id="body"
                        name="body"
                        defaultValue="If you see this, Web Push is working."
                        required
                    />
                    <InputError message={errors.body} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="actionUrl">Action URL</Label>
                    <Input
                        id="actionUrl"
                        name="actionUrl"
                        defaultValue="/launch"
                        required
                    />
                    <InputError message={errors.actionUrl} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="target">Target</Label>
                    <select
                        id="target"
                        name="target"
                        defaultValue="self"
                        className="h-9 rounded-md border border-input px-3 text-sm"
                    >
                        <option value="self">My account</option>
                        <option value="email">User email</option>
                        <option value="all">All subscribers</option>
                    </select>
                    <InputError message={errors.target} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="targetEmail">
                        User email (when target is email)
                    </Label>
                    <Input
                        id="targetEmail"
                        name="targetEmail"
                        type="email"
                        placeholder="user@example.com"
                    />
                    <InputError message={errors.targetEmail} />
                </div>

                <Button type="submit">Send test push</Button>
            </Form>
        </>
    );
}

AdminNotificationsTestIndex.layout = {
    breadcrumbs: [
        {
            title: 'Push test',
            href: '/admin/notifications-test',
        },
    ],
};
