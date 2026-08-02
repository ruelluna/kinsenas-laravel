import { Link } from '@inertiajs/react';
import FundCardHeader from '@/components/savings/fund-card-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatMoney } from '@/lib/format-money';
import { remainingTone } from '@/lib/fund-balance-tone';
import type { FundBalance } from '@/types/savings';

type FundAction = {
    label: string | ((balance: FundBalance) => string);
    onClick: (categoryId: string) => void;
};

type Props = {
    fundBalances: FundBalance[];
    variant?: 'compact' | 'detailed';
    hasLockedIncome?: boolean;
    spendHref?: string;
    onSpendFrom?: (categoryId: string) => void;
    limit?: number;
    showReceived?: boolean;
    transferredLabel?: string;
    action?: FundAction;
};

export default function FundBalanceGrid({
    fundBalances,
    variant = 'compact',
    hasLockedIncome = false,
    spendHref,
    onSpendFrom,
    limit,
    showReceived = false,
    transferredLabel = 'Transferred',
    action,
}: Props) {
    const balances = limit !== undefined ? fundBalances.slice(0, limit) : fundBalances;

    if (balances.length === 0) {
        return null;
    }

    function renderAction(balance: FundBalance) {
        if (hasLockedIncome && action) {
            const label = typeof action.label === 'function' ? action.label(balance) : action.label;

            return (
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    className="mt-4 w-full"
                    onClick={() => action.onClick(balance.categoryId)}
                >
                    {label}
                </Button>
            );
        }

        if (hasLockedIncome && onSpendFrom) {
            return (
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    className="mt-4 w-full"
                    onClick={() => onSpendFrom(balance.categoryId)}
                >
                    Spend from {balance.name}
                </Button>
            );
        }

        if (hasLockedIncome && spendHref && !onSpendFrom && !action) {
            return (
                <Button variant="outline" size="sm" className="mt-4 w-full" asChild>
                    <Link href={spendHref}>Spend from {balance.name}</Link>
                </Button>
            );
        }

        return null;
    }

    if (variant === 'compact') {
        return (
            <ul className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                {balances.map((balance) => (
                    <li
                        key={balance.categoryId}
                        className={`rounded-lg border p-4 ${balance.isDefault ? 'ring-2 ring-primary/20' : ''}`}
                    >
                        <FundCardHeader {...balance} />
                        <div className="mt-3 flex items-end justify-between gap-2">
                            <div>
                                <p className="text-xs text-muted-foreground">Remaining</p>
                                <p className={`text-lg font-semibold ${remainingTone(balance.percentUsed)}`}>
                                    {formatMoney(balance.remaining)}
                                </p>
                            </div>
                            {balance.percentUsed !== null && (
                                <Badge variant="outline">{balance.percentUsed}% used</Badge>
                            )}
                        </div>
                    </li>
                ))}
            </ul>
        );
    }

    return (
        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            {balances.map((balance) => (
                <div
                    key={balance.categoryId}
                    className={`rounded-lg border p-4 ${balance.isDefault ? 'ring-2 ring-primary/20' : ''}`}
                >
                    <FundCardHeader {...balance} />
                    <dl className="mt-4 space-y-1 text-sm">
                        {balance.openingBalance !== null
                            && parseFloat(balance.openingBalance) > 0 && (
                            <div className="flex justify-between gap-2">
                                <dt className="text-muted-foreground">Existing savings</dt>
                                <dd>{formatMoney(balance.openingBalance)}</dd>
                            </div>
                        )}
                        <div className="flex justify-between gap-2">
                            <dt className="text-muted-foreground">Allocated</dt>
                            <dd>{formatMoney(balance.allocated)}</dd>
                        </div>
                        <div className="flex justify-between gap-2">
                            <dt className="text-muted-foreground">{transferredLabel}</dt>
                            <dd>{formatMoney(balance.transferred)}</dd>
                        </div>
                        {showReceived && (
                            <div className="flex justify-between gap-2">
                                <dt className="text-muted-foreground">Received</dt>
                                <dd>{formatMoney(balance.received)}</dd>
                            </div>
                        )}
                        <div className="flex justify-between gap-2">
                            <dt className="text-muted-foreground">Spent</dt>
                            <dd>{formatMoney(balance.spent)}</dd>
                        </div>
                        <div className="flex justify-between gap-2 font-medium">
                            <dt>Remaining</dt>
                            <dd className={remainingTone(balance.percentUsed)}>
                                {formatMoney(balance.remaining)}
                            </dd>
                        </div>
                    </dl>
                    {renderAction(balance)}
                </div>
            ))}
        </div>
    );
}
