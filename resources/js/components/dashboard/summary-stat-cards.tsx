import { AlertTriangle, Layers, Wallet } from 'lucide-react';
import type { ReactNode } from 'react';
import { formatMoney } from '@/lib/format-money';
import type { DashboardSummary, DashboardSetup } from '@/types/dashboard';

type Props = {
    setup: DashboardSetup;
    summary: DashboardSummary;
};

export default function SummaryStatCards({ setup, summary }: Props) {
    if (!setup.hasPlan) {
        return null;
    }

    return (
        <div className="grid gap-4 md:grid-cols-3">
            <StatCard
                icon={Wallet}
                title={summary.defaultFundName ?? 'Everyday fund'}
                value={formatMoney(summary.defaultFundRemaining)}
                description={defaultFundDescription(summary.defaultFundName)}
            />
            <StatCard
                icon={Layers}
                title="Other funds"
                value={formatMoney(summary.otherFundsRemaining)}
                description="All other fund buckets"
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

function defaultFundDescription(name: string | null): string {
    if (name === 'Everyday Fund') {
        return 'Daily expenses';
    }

    return 'Remaining balance';
}

function attentionDescription(summary: DashboardSummary): string {
    const parts: string[] = [];

    if (summary.pendingTransferCount > 0) {
        parts.push(
            `${summary.pendingTransferCount} pending transfer${summary.pendingTransferCount === 1 ? '' : 's'}`,
        );
    }

    if (summary.pendingSpendCount > 0) {
        parts.push(
            `${summary.pendingSpendCount} pending spend${summary.pendingSpendCount === 1 ? '' : 's'}`,
        );
    }

    if (summary.awaitingReimbursementCount > 0) {
        parts.push(
            `${summary.awaitingReimbursementCount} awaiting payback`,
        );
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
                    : 'rounded-xl border border-border bg-surface p-4'
            }
        >
            <div className="flex items-center gap-2 text-sm text-muted-foreground">
                <Icon className="size-4" />
                {title}
            </div>
            <p className="mt-2 font-space text-xl font-semibold tracking-tight md:text-2xl">
                {value}
            </p>
            <p className="mt-1 text-sm text-muted-foreground">{description}</p>
        </div>
    );
}
