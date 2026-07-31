import { Form, Head, usePage } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Transfer } from '@/types/savings';
import type { SharedData } from '@/types';

type Props = {
    lockedPeriods: Array<{ id: string; periodStart: string; amount: string | null }>;
    banks: Array<{ id: string; name: string }>;
    recipients: Array<{ id: string; name: string }>;
    categories: Array<{ id: string; name: string }>;
    transfers: Transfer[];
};

export default function TransfersIndex({ lockedPeriods, banks, recipients, categories, transfers }: Props) {
    const { currentTeam } = usePage<SharedData>().props;
    const teamSlug = currentTeam?.slug ?? '';

    return (
        <>
            <Head title="Transfers" />
            <Heading variant="small" title="Transfers" description="Record and confirm transfers from locked income." />

            {lockedPeriods.length > 0 && (
                <Form action={`/${teamSlug}/savings/transfers`} method="post" className="mt-6 grid max-w-lg gap-4 rounded-lg border p-4">
                    <div className="grid gap-2">
                        <Label>Income period</Label>
                        <select name="income_period_id" className="border-input h-9 rounded-md border px-3 text-sm" required>
                            {lockedPeriods.map((p) => (
                                <option key={p.id} value={p.id}>{p.periodStart} — {p.amount ?? '—'}</option>
                            ))}
                        </select>
                    </div>
                    <div className="grid gap-2">
                        <Label>Category</Label>
                        <select name="category_id" className="border-input h-9 rounded-md border px-3 text-sm" required>
                            {categories.map((c) => (
                                <option key={c.id} value={c.id}>{c.name}</option>
                            ))}
                        </select>
                    </div>
                    <div className="grid gap-2">
                        <Label>Bank</Label>
                        <select name="bank_id" className="border-input h-9 rounded-md border px-3 text-sm" required>
                            {banks.map((b) => (
                                <option key={b.id} value={b.id}>{b.name}</option>
                            ))}
                        </select>
                    </div>
                    <div className="grid gap-2">
                        <Label>Recipient</Label>
                        <select name="recipient_id" className="border-input h-9 rounded-md border px-3 text-sm" required>
                            {recipients.map((r) => (
                                <option key={r.id} value={r.id}>{r.name}</option>
                            ))}
                        </select>
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="amount">Amount</Label>
                        <Input id="amount" name="amount" type="number" step="0.01" min="0.01" required />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="transferred_on">Transfer date</Label>
                        <Input id="transferred_on" name="transferred_on" type="date" required />
                    </div>
                    <Button type="submit">Record transfer</Button>
                </Form>
            )}

            <div className="mt-8 space-y-3">
                {transfers.map((t) => (
                    <div key={t.id} className="flex items-center justify-between rounded-lg border p-3 text-sm">
                        <div>
                            <p className="font-medium">{t.amount ?? '—'} → {t.recipientName}</p>
                            <p className="text-muted-foreground">{t.categoryName} · {t.bankName} · {t.transferredOn}</p>
                        </div>
                        {t.status === 'pending' && (
                            <Form action={`/${teamSlug}/savings/transfers/${t.id}/confirm`} method="post">
                                <Button type="submit" size="sm">Confirm</Button>
                            </Form>
                        )}
                    </div>
                ))}
            </div>
        </>
    );
}

TransfersIndex.layout = (page: { props: SharedData }) => ({
    breadcrumbs: [{ title: 'Transfers', href: `/${page.props.currentTeam?.slug}/savings/transfers` }],
});
