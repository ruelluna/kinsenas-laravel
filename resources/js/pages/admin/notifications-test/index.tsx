import { Form, Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const pushFormDefaults = {
    title: 'Kinsenas test push',
    body: 'If you see this, Web Push is working.',
    actionUrl: '/dashboard',
    target: 'self',
    targetEmail: '',
};

type Props = {
    subscriberCount: number;
};

export default function AdminNotificationsTestIndex({
    subscriberCount = 0,
}: Props) {
    return (
        <>
            <Head title="Admin — Push test" />
            <Heading
                variant="small"
                title="Push test"
                description="Send a test Web Push to yourself, a user email, or all subscribers."
            />

            <p className="mt-4 text-sm text-muted-foreground">
                Active push subscribers: {subscriberCount}
            </p>

            <Form
                method="post"
                action="/admin/notifications-test"
                className="mt-6 max-w-xl space-y-4"
                defaults={pushFormDefaults}
            >
                {({ data, setData, processing, errors }) => (
                    <>
                        <div className="grid gap-2">
                            <Label htmlFor="title">Title</Label>
                            <Input
                                id="title"
                                name="title"
                                value={data.title}
                                onChange={(event) =>
                                    setData('title', event.target.value)
                                }
                            />
                            <InputError message={errors.title} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="body">Body</Label>
                            <Input
                                id="body"
                                name="body"
                                value={data.body}
                                onChange={(event) =>
                                    setData('body', event.target.value)
                                }
                            />
                            <InputError message={errors.body} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="actionUrl">Action URL</Label>
                            <Input
                                id="actionUrl"
                                name="actionUrl"
                                value={data.actionUrl}
                                onChange={(event) =>
                                    setData('actionUrl', event.target.value)
                                }
                            />
                            <InputError message={errors.actionUrl} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="target">Target</Label>
                            <select
                                id="target"
                                name="target"
                                value={data.target}
                                onChange={(event) =>
                                    setData('target', event.target.value)
                                }
                                className="h-9 rounded-md border border-input px-3 text-sm"
                            >
                                <option value="self">My account</option>
                                <option value="email">User email</option>
                                <option value="all">All subscribers</option>
                            </select>
                            <InputError message={errors.target} />
                        </div>

                        {data.target === 'email' && (
                            <div className="grid gap-2">
                                <Label htmlFor="targetEmail">User email</Label>
                                <Input
                                    id="targetEmail"
                                    name="targetEmail"
                                    type="email"
                                    value={data.targetEmail}
                                    onChange={(event) =>
                                        setData(
                                            'targetEmail',
                                            event.target.value,
                                        )
                                    }
                                />
                                <InputError message={errors.targetEmail} />
                            </div>
                        )}

                        <Button type="submit" disabled={processing}>
                            Send test push
                        </Button>
                    </>
                )}
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
