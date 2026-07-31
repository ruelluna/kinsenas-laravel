import { Head, Link, usePage } from '@inertiajs/react';
import Heading from '@/components/heading';
import { formatMoney } from '@/lib/format-money';
import type { ReportTotals } from '@/types/savings';
import type { SharedData } from '@/types';

type Props = { totals: ReportTotals };

export default function SavingsReports({ totals }: Props) {
    const { currentTeam } = usePage<SharedData>().props;
    const teamSlug = currentTeam?.slug ?? '';

    return (
        <>
            <Head title="Reports" />
            <Heading
                variant="small"
                title="Reports"
                description="Fund health and confirmed spending totals (decrypted in your session only)."
            />

            <div className="mt-6">
                <h3 className="font-medium">Fund health</h3>
                <div className="mt-3 overflow-hidden rounded-lg border">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b bg-muted/50 text-left">
                                <th className="px-4 py-3 font-medium">Fund</th>
                                <th className="px-4 py-3 font-medium text-right">Allocated</th>
                                <th className="px-4 py-3 font-medium text-right">Spent</th>
                                <th className="px-4 py-3 font-medium text-right">Remaining</th>
                                <th className="px-4 py-3 font-medium text-right">Used</th>
                            </tr>
                        </thead>
                        <tbody>
                            {totals.fund_health.length === 0 ? (
                                <tr>
                                    <td colSpan={5} className="px-4 py-6 text-center text-muted-foreground">
                                        Lock income and record spending to see fund health.
                                    </td>
                                </tr>
                            ) : (
                                totals.fund_health.map((row) => (
                                    <tr key={row.category_id} className="border-b last:border-b-0">
                                        <td className="px-4 py-3">{row.category_name}</td>
                                        <td className="px-4 py-3 text-right">{formatMoney(row.allocated)}</td>
                                        <td className="px-4 py-3 text-right">{formatMoney(row.spent)}</td>
                                        <td className="px-4 py-3 text-right font-medium">
                                            {formatMoney(row.remaining)}
                                        </td>
                                        <td className="px-4 py-3 text-right">{row.percent_used}%</td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            <div className="mt-8 grid gap-6 md:grid-cols-2">
                <ReportSection
                    title="By bank"
                    items={totals.by_bank.map((row) => ({ label: row.bank_name, total: row.total }))}
                    emptyMessage="No bank spending recorded yet."
                />
                <ReportSection
                    title="By recipient"
                    items={totals.by_recipient.map((row) => ({ label: row.recipient_name, total: row.total }))}
                    emptyMessage="No recipient spending recorded yet."
                />
            </div>

            <p className="mt-6 text-sm text-muted-foreground">
                <Link href={`/${teamSlug}/savings/spending`} className="text-primary underline-offset-4 hover:underline">
                    Record spending →
                </Link>
            </p>
        </>
    );
}

function ReportSection({
    title,
    items,
    emptyMessage,
}: {
    title: string;
    items: Array<{ label: string; total: string }>;
    emptyMessage: string;
}) {
    return (
        <div className="rounded-lg border p-4">
            <h3 className="font-medium">{title}</h3>
            <ul className="mt-3 space-y-2 text-sm">
                {items.length === 0 ? (
                    <li className="text-muted-foreground">{emptyMessage}</li>
                ) : (
                    items.map((item) => (
                        <li key={item.label} className="flex justify-between gap-2">
                            <span>{item.label}</span>
                            <span className="font-medium">{formatMoney(item.total)}</span>
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
