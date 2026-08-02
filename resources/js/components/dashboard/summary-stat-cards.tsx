import { Link } from '@inertiajs/react';
import { AlertTriangle, Landmark, Wallet } from 'lucide-react';
import type { ReactNode } from 'react';
import { formatMoney } from '@/lib/format-money';
import type { DashboardQuickLinks, DashboardSummary, DashboardSetup } from '@/types/dashboard';

type Props = {
    setup: DashboardSetup;
    summary: DashboardSummary;
    quickLinks: DashboardQuickLinks;
};

export default function SummaryStatCards({ setup, summary, quickLinks }: Props) {
    if (!setup.hasPlan) {
        return null;
    }

    if (!setup.hasLockedIncome && !setup.hasOpeningBalances) {
        return (
            <div className="rounded-lg border border-dashed p-4 text-sm text-muted-foreground">
                Lock income or add existing savings on your plan to see balances.{' '}
                <Link href={quickLinks.plan} className="text-primary underline-offset-4 hover:underline">
                    Savings Plan
                </Link>
                {' · '}
                <Link href={quickLinks.income} className="text-primary underline-offset-4 hover:underline">
                    Income
                </Link>
            </div>
        );
    }

    return (
        <div className="grid gap-4 md:grid-cols-3">
            <StatCard
                icon={Wallet}
                title="Total remaining"
                value={formatMoney(summary.totalRemaining)}
                description="Across all fund buckets"
            />
            <StatCard
                icon={Landmark}
                title="In banks"
                value={formatMoney(summary.totalInBanks)}
                description={
                    <Link href={quickLinks.banks} className="text-primary underline-offset-4 hover:underline">
                        View banks
                    </Link>
                }
            />
            <StatCard
                icon={AlertTriangle}
                title="Needs attention"
                value={String(summary.attentionCount)}
                description={attentionDescription(summary)}
                tone={summary.attentionCount > 0 ? 'warning' : 'default'}
            />
        </div>
    );
}

function attentionDescription(summary: DashboardSummary): string {
    const parts: string[] = [];

    if (summary.pendingTransferCount > 0) {
        parts.push(
            `${summary.pendingTransferCount} pending transfer${summary.pendingTransferCount === 1 ? '' : 's'}`,
        );
    }

    if (summary.pendingSpendCount > 0) {
        parts.push(`${summary.pendingSpendCount} pending spend${summary.pendingSpendCount === 1 ? '' : 's'}`);
    }

    if (summary.lowBalanceFunds.length > 0) {
        parts.push(
            `${summary.lowBalanceFunds.length} low fund bucket${summary.lowBalanceFunds.length === 1 ? '' : 's'}`,
        );
    }

    if (parts.length === 0) {
        return 'Nothing waiting on you';
    }

    return parts.join(' · ');
}

function StatCard({
    icon: Icon,
    title,
    value,
    description,
    tone = 'default',
}: {
    icon: typeof Wallet;
    title: string;
    value: string;
    description: ReactNode;
    tone?: 'default' | 'warning';
}) {
    return (
        <div
            className={
                tone === 'warning'
                    ? 'rounded-xl border border-warning/30 bg-warning/5 p-4'
                    : 'rounded-xl border p-4'
            }
        >
            <div className="flex items-center gap-2 text-sm text-muted-foreground">
                <Icon className="size-4" />
                {title}
            </div>
            <p className="mt-2 text-2xl font-semibold tracking-tight">{value}</p>
            <p className="mt-1 text-sm text-muted-foreground">{description}</p>
        </div>
    );
}
