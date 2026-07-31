import { Head, usePage } from '@inertiajs/react';
import Heading from '@/components/heading';
import type { ReportTotals } from '@/types/savings';
import type { SharedData } from '@/types';

type Props = { totals: ReportTotals };

export default function SavingsReports({ totals }: Props) {
    return (
        <>
            <Head title="Reports" />
            <Heading variant="small" title="Reports" description="Confirmed transfer totals (decrypted in your session only)." />

            <div className="mt-6 grid gap-6 md:grid-cols-3">
                <ReportSection title="By bank" items={totals.by_bank.map((r) => ({ label: r.bank_name, total: r.total }))} />
                <ReportSection title="By recipient" items={totals.by_recipient.map((r) => ({ label: r.recipient_name, total: r.total }))} />
                <ReportSection title="By category" items={totals.by_category.map((r) => ({ label: r.category_name, total: r.total }))} />
            </div>
        </>
    );
}

function ReportSection({ title, items }: { title: string; items: Array<{ label: string; total: string }> }) {
    return (
        <div className="rounded-lg border p-4">
            <h3 className="font-medium">{title}</h3>
            <ul className="mt-3 space-y-2 text-sm">
                {items.length === 0 ? (
                    <li className="text-muted-foreground">No confirmed transfers yet.</li>
                ) : (
                    items.map((item) => (
                        <li key={item.label} className="flex justify-between gap-2">
                            <span>{item.label}</span>
                            <span className="font-medium">₱{item.total}</span>
                        </li>
                    ))
                )}
            </ul>
        </div>
    );
}

SavingsReports.layout = (props: SharedData) => ({
    breadcrumbs: [{ title: 'Reports', href: `/${props.currentTeam?.slug}/savings/reports` }],
});
