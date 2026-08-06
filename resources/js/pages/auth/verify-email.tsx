// Components
import { Form, Head, usePage } from '@inertiajs/react';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { BETA_FREE_MESSAGE } from '@/lib/beta-copy';
import { logout } from '@/routes';
import { send } from '@/routes/verification';
import type { SharedData } from '@/types';

export default function VerifyEmail({ status }: { status?: string }) {
    const { openBeta } = usePage<SharedData>().props;

    return (
        <>
            <Head title="Email verification" />

            <div className="mb-6 space-y-2 text-center text-sm text-muted-foreground">
                <p>
                    Verify your email to access Kinsenas. We sent a link to the
                    address you used when you signed up.
                </p>
                {openBeta.isActive && (
                    <p>
                        After you verify your email, your beta application goes
                        to our team for review. Once approved, you can use the
                        core savings planner with your real account — free
                        during beta. {BETA_FREE_MESSAGE}
                    </p>
                )}
            </div>

            {status === 'verification-link-sent' && (
                <div className="mb-4 text-center text-sm font-medium text-success">
                    A new verification link has been sent to the email address
                    you provided during registration.
                </div>
            )}

            <Form {...send.form()} className="space-y-6 text-center">
                {({ processing }) => (
                    <>
                        <Button disabled={processing} variant="secondary">
                            {processing && <Spinner />}
                            Resend verification email
                        </Button>

                        <TextLink
                            href={logout()}
                            className="mx-auto block text-sm"
                        >
                            Log out
                        </TextLink>
                    </>
                )}
            </Form>
        </>
    );
}

VerifyEmail.layout = {
    title: 'Email verification',
    description:
        'Please verify your email address by clicking on the link we just emailed to you.',
};
