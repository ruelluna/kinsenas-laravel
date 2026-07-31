import { Form, Head, usePage } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Recipient } from '@/types/savings';
import type { SharedData } from '@/types';

type Props = {
    recipients: Recipient[];
    recipientTypes: Array<{ value: string; label: string }>;
};

export default function RecipientsIndex({ recipients, recipientTypes }: Props) {
    const { currentTeam } = usePage<SharedData>().props;
    const teamSlug = currentTeam?.slug ?? '';

    return (
        <>
            <Head title="Recipients" />
            <Heading variant="small" title="Recipients" description="People and organizations receiving payments." />

            <Form action={`/${teamSlug}/savings/recipients`} method="post" className="mt-6 grid max-w-md gap-4 rounded-lg border p-4">
                <div className="grid gap-2">
                    <Label htmlFor="type">Type</Label>
                    <select id="type" name="type" className="border-input h-9 rounded-md border px-3 text-sm">
                        {recipientTypes.map((t) => (
                            <option key={t.value} value={t.value}>{t.label}</option>
                        ))}
                    </select>
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="name">Name</Label>
                    <Input id="name" name="name" required />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="notes">Notes</Label>
                    <Input id="notes" name="notes" />
                </div>
                <Button type="submit">Add recipient</Button>
            </Form>

            <ul className="mt-8 space-y-2">
                {recipients.map((r) => (
                    <li key={r.id} className="rounded-lg border p-3 text-sm">
                        <span className="font-medium">{r.name}</span>
                        <span className="text-muted-foreground"> — {r.typeLabel}</span>
                    </li>
                ))}
            </ul>
        </>
    );
}

RecipientsIndex.layout = (props: SharedData) => ({
    breadcrumbs: [{ title: 'Recipients', href: `/${props.currentTeam?.slug}/savings/recipients` }],
});
