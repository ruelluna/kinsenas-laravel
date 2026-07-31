import { Form, Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { formatMoneyFromCents } from '@/lib/format-money';
import type { AdminPaymentSubmission, FilterOption } from '@/types/billing';

type Props = {
    submissions: AdminPaymentSubmission[];
    filters: {
        status: string;
    };
    statusOptions: FilterOption[];
};

export default function AdminPaymentSubmissionsIndex({
    submissions,
    filters,
    statusOptions,
}: Props) {
    return (
        <>
            <Head title="Admin — Payments" />
            <Heading variant="small" title="Payment submissions" description="Approve or reject manual PayMaya payments." />

            <Form method="get" action="/admin/payment-submissions" className="mt-6 flex flex-wrap items-end gap-3">
                <div className="grid gap-2">
                    <Label htmlFor="status">Status</Label>
                    <select
                        id="status"
                        name="status"
                        defaultValue={filters.status}
                        className="border-input h-9 rounded-md border px-3 text-sm"
                    >
                        {statusOptions.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                </div>
                <Button type="submit" variant="outline">
                    Filter
                </Button>
            </Form>

            <div className="mt-6 space-y-4">
                {submissions.length === 0 && (
                    <p className="text-sm text-muted-foreground">No submissions for this filter.</p>
                )}
                {submissions.map((s) => (
                    <div key={s.id} className="rounded-lg border p-4 text-sm">
                        <p className="font-medium">
                            {s.userName} — {s.referenceNumber}
                        </p>
                        <p className="text-muted-foreground">
                            {s.userEmail} · {s.planName} · {s.interval} · {s.status}
                        </p>
                        {s.amount !== null && (
                            <p className="text-muted-foreground">{formatMoneyFromCents(s.amount)}</p>
                        )}
                        {s.proofImageUrl && (
                            <a
                                href={s.proofImageUrl}
                                target="_blank"
                                rel="noreferrer"
                                className="mt-2 inline-block"
                            >
                                <img
                                    src={s.proofImageUrl}
                                    alt="Payment proof"
                                    className="max-h-40 rounded border"
                                />
                            </a>
                        )}
                        {s.notes && <p className="mt-2 text-muted-foreground">Notes: {s.notes}</p>}
                        {s.status === 'pending' && (
                            <div className="mt-3 flex flex-col gap-3 sm:flex-row sm:items-start">
                                <Form action={`/admin/payment-submissions/${s.id}/approve`} method="post">
                                    <Button type="submit" size="sm">
                                        Approve
                                    </Button>
                                </Form>
                                <Form
                                    action={`/admin/payment-submissions/${s.id}/reject`}
                                    method="post"
                                    className="flex flex-1 flex-col gap-2 sm:max-w-md"
                                >
                                    <Label htmlFor={`notes-${s.id}`}>Reject notes</Label>
                                    <textarea
                                        id={`notes-${s.id}`}
                                        name="notes"
                                        required
                                        minLength={3}
                                        className="border-input min-h-20 rounded-md border px-3 py-2 text-sm"
                                        placeholder="Reason for rejection"
                                    />
                                    <Button type="submit" size="sm" variant="outline">
                                        Reject
                                    </Button>
                                </Form>
                            </div>
                        )}
                    </div>
                ))}
            </div>
        </>
    );
}
