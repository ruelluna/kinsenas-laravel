import { Form, Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatMoneyFromCents } from '@/lib/format-money';
import type { AdminPaymentSubmission, AdminSubscriber, FilterOption } from '@/types/billing';

type Props = {
    subscriber: AdminSubscriber;
    paymentSubmissions: AdminPaymentSubmission[];
    plans: Array<{ id: string; name: string; slug: string }>;
    intervalOptions: FilterOption[];
};

export default function AdminSubscribersShow({
    subscriber,
    paymentSubmissions,
    plans,
    intervalOptions,
}: Props) {
    const subscription = subscriber.subscription;

    return (
        <>
            <Head title={`Admin — ${subscriber.name}`} />
            <div className="mb-4">
                <Button variant="ghost" size="sm" asChild>
                    <Link href="/admin/subscribers">
                        <ArrowLeft className="size-4" />
                        Back to subscribers
                    </Link>
                </Button>
            </div>
            <Heading
                variant="small"
                title={subscriber.name}
                description={subscriber.email}
            />

            <section className="mt-6 rounded-lg border p-4 text-sm">
                <h2 className="font-medium">Subscription</h2>
                {subscription ? (
                    <dl className="mt-3 grid gap-2 text-muted-foreground">
                        <div>Status: {subscription.statusLabel}</div>
                        <div>Plan: {subscription.planName ?? '—'}</div>
                        <div>
                            Trial ends:{' '}
                            {subscription.trialEndsAt
                                ? new Date(subscription.trialEndsAt).toLocaleString()
                                : '—'}
                        </div>
                        <div>
                            Period ends:{' '}
                            {subscription.currentPeriodEndsAt
                                ? new Date(subscription.currentPeriodEndsAt).toLocaleString()
                                : '—'}
                        </div>
                        <div>Access: {subscription.hasAccess ? 'Yes' : 'No'}</div>
                    </dl>
                ) : (
                    <p className="mt-2 text-muted-foreground">No subscription record.</p>
                )}
            </section>

            <div className="mt-6 grid gap-6 lg:grid-cols-2">
                <section className="rounded-lg border p-4">
                    <h2 className="text-sm font-medium">Extend trial</h2>
                    <Form
                        action={`/admin/subscribers/${subscriber.id}/extend-trial`}
                        method="post"
                        className="mt-3 flex flex-wrap items-end gap-3"
                    >
                        <div className="grid gap-2">
                            <Label htmlFor="days">Days</Label>
                            <Input id="days" name="days" type="number" min={1} max={90} defaultValue={7} required />
                        </div>
                        <Button type="submit" size="sm" disabled={!subscription}>
                            Extend
                        </Button>
                    </Form>
                </section>

                <section className="rounded-lg border p-4">
                    <h2 className="text-sm font-medium">Activate manually</h2>
                    <Form
                        action={`/admin/subscribers/${subscriber.id}/activate`}
                        method="post"
                        className="mt-3 space-y-3"
                    >
                        <div className="grid gap-2">
                            <Label htmlFor="interval">Billing interval</Label>
                            <select
                                id="interval"
                                name="interval"
                                className="border-input h-9 rounded-md border px-3 text-sm"
                                required
                            >
                                {intervalOptions.map((option) => (
                                    <option key={option.value} value={option.value}>
                                        {option.label}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="plan_id">Plan (optional)</Label>
                            <select
                                id="plan_id"
                                name="plan_id"
                                className="border-input h-9 rounded-md border px-3 text-sm"
                            >
                                <option value="">Keep current plan</option>
                                {plans.map((plan) => (
                                    <option key={plan.id} value={plan.id}>
                                        {plan.name}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <Button type="submit" size="sm">Activate</Button>
                    </Form>
                </section>

                <section className="rounded-lg border p-4">
                    <h2 className="text-sm font-medium">Change plan</h2>
                    <Form
                        action={`/admin/subscribers/${subscriber.id}/change-plan`}
                        method="post"
                        className="mt-3 flex flex-wrap items-end gap-3"
                    >
                        <div className="grid gap-2">
                            <Label htmlFor="change_plan_id">Plan</Label>
                            <select
                                id="change_plan_id"
                                name="plan_id"
                                className="border-input h-9 rounded-md border px-3 text-sm"
                                required
                                disabled={!subscription}
                            >
                                {plans.map((plan) => (
                                    <option key={plan.id} value={plan.id}>
                                        {plan.name}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <Button type="submit" size="sm" disabled={!subscription}>
                            Change plan
                        </Button>
                    </Form>
                </section>

                <section className="rounded-lg border p-4">
                    <h2 className="text-sm font-medium">Cancel subscription</h2>
                    <Form
                        action={`/admin/subscribers/${subscriber.id}/cancel`}
                        method="post"
                        className="mt-3 space-y-3"
                    >
                        <div className="grid gap-2">
                            <Label htmlFor="reason">Reason (optional)</Label>
                            <textarea
                                id="reason"
                                name="reason"
                                className="border-input min-h-20 w-full rounded-md border px-3 py-2 text-sm"
                            />
                        </div>
                        <Button type="submit" size="sm" variant="destructive" disabled={!subscription}>
                            Cancel
                        </Button>
                    </Form>
                </section>
            </div>

            <section className="mt-8">
                <h2 className="text-sm font-medium">Payment history</h2>
                <div className="mt-3 space-y-3">
                    {paymentSubmissions.length === 0 && (
                        <p className="text-sm text-muted-foreground">No payment submissions.</p>
                    )}
                    {paymentSubmissions.map((submission) => (
                        <div key={submission.id} className="rounded-lg border p-4 text-sm">
                            <p className="font-medium">{submission.referenceNumber}</p>
                            <p className="text-muted-foreground">
                                {submission.planName} · {submission.interval} · {submission.status}
                            </p>
                            {submission.amount !== null && (
                                <p className="text-muted-foreground">
                                    {formatMoneyFromCents(submission.amount)}
                                </p>
                            )}
                            {submission.notes && (
                                <p className="mt-1 text-muted-foreground">Notes: {submission.notes}</p>
                            )}
                        </div>
                    ))}
                </div>
            </section>
        </>
    );
}
