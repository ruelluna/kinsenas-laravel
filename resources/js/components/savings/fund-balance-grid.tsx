import { Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatMoney } from '@/lib/format-money';
import { remainingTone } from '@/lib/fund-balance-tone';
import type { FundBalance } from '@/types/savings';

type Props = {
    fundBalances: FundBalance[];
    variant?: 'compact' | 'detailed';
    hasLockedIncome?: boolean;
    spendHref?: string;
    onSpendFrom?: (categoryId: string) => void;
    limit?: number;
};

export default function FundBalanceGrid({
    fundBalances,
    variant = 'compact',
    hasLockedIncome = false,
    spendHref,
    onSpendFrom,
    limit,
}: Props) {
    const balances = limit !== undefined ? fundBalances.slice(0, limit) : fundBalances;

    if (balances.length === 0) {
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
                        <div className="flex items-start justify-between gap-2">
                            <div>
                                <p className="font-medium">{balance.name}</p>
                                {balance.hint && (
                                    <p className="mt-0.5 text-xs text-muted-foreground">{balance.hint}</p>
                                )}
                            </div>
                            {balance.isDefault && <Badge variant="secondary">Default</Badge>}
                        </div>
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
                    <div className="flex items-start justify-between gap-2">
                        <div>
                            <p className="font-medium">{balance.name}</p>
                            {balance.hint && (
                                <p className="mt-0.5 text-xs text-muted-foreground">{balance.hint}</p>
                            )}
                        </div>
                        {balance.isDefault && <Badge variant="secondary">Default</Badge>}
                    </div>
                    <dl className="mt-4 space-y-1 text-sm">
                        <div className="flex justify-between gap-2">
                            <dt className="text-muted-foreground">Allocated</dt>
                            <dd>{formatMoney(balance.allocated)}</dd>
                        </div>
                        <div className="flex justify-between gap-2">
                            <dt className="text-muted-foreground">Transferred</dt>
                            <dd>{formatMoney(balance.transferred)}</dd>
                        </div>
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
                    {hasLockedIncome && onSpendFrom && (
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            className="mt-4 w-full"
                            onClick={() => onSpendFrom(balance.categoryId)}
                        >
                            Spend from {balance.name}
                        </Button>
                    )}
                    {hasLockedIncome && spendHref && !onSpendFrom && (
                        <Button variant="outline" size="sm" className="mt-4 w-full" asChild>
                            <Link href={spendHref}>Spend from {balance.name}</Link>
                        </Button>
                    )}
                </div>
            ))}
        </div>
    );
}
