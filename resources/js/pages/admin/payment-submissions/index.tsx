import { Form, Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';

type Props = {
    submissions: Array<{
        id: string;
        referenceNumber: string;
        status: string;
        userName: string | null;
        userEmail: string | null;
        planName: string | null;
        interval: string | null;
        amount: number | null;
        createdAt: string;
    }>;
};

export default function AdminPaymentSubmissionsIndex({ submissions }: Props) {
    return (
        <>
            <Head title="Admin — Payments" />
            <Heading variant="small" title="Payment submissions" description="Approve or reject manual PayMaya payments." />
            <div className="mt-6 space-y-4">
                {submissions.map((s) => (
                    <div key={s.id} className="rounded-lg border p-4 text-sm">
                        <p className="font-medium">{s.userName} — {s.referenceNumber}</p>
                        <p className="text-muted-foreground">{s.planName} · {s.interval} · {s.status}</p>
                        {s.status === 'pending' && (
                            <div className="mt-3 flex gap-2">
                                <Form action={`/admin/payment-submissions/${s.id}/approve`} method="post">
                                    <Button type="submit" size="sm">Approve</Button>
                                </Form>
                                <Form action={`/admin/payment-submissions/${s.id}/reject`} method="post">
                                    <Button type="submit" size="sm" variant="outline">Reject</Button>
                                </Form>
                            </div>
                        )}
                    </div>
                ))}
            </div>
        </>
    );
}
