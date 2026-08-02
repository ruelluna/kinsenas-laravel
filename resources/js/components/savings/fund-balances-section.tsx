import { Link } from '@inertiajs/react';
import FundBalanceGrid from '@/components/savings/fund-balance-grid';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import type { FundBalance } from '@/types/savings';

type Props = {
    title: string;
    description: string;
    fundBalances: FundBalance[];
    spendHref: string;
    hasLockedIncome?: boolean;
    limit?: number;
    bordered?: boolean;
    className?: string;
};

export default function FundBalancesSection({
    title,
    description,
    fundBalances,
    spendHref,
    hasLockedIncome = false,
    limit,
    bordered = false,
    className,
}: Props) {
    if (fundBalances.length === 0) {
        return null;
    }

    const content = (
        <>
            <div className="flex flex-wrap items-center justify-between gap-3">
                <div>
                    {bordered ? (
                        <h3 className="font-medium">{title}</h3>
                    ) : (
                        <h2 className="font-medium">{title}</h2>
                    )}
                    <p className="mt-1 text-sm text-muted-foreground">{description}</p>
                </div>
                <Button variant="outline" size="sm" asChild>
                    <Link href={spendHref}>Record spending</Link>
                </Button>
            </div>
            <div className="mt-4">
                <FundBalanceGrid
                    fundBalances={fundBalances}
                    variant="compact"
                    hasLockedIncome={hasLockedIncome}
                    spendHref={spendHref}
                    limit={limit}
                />
            </div>
        </>
    );

    if (bordered) {
        return <div className={cn('rounded-lg border p-4', className)}>{content}</div>;
    }

    return <section className={className}>{content}</section>;
}
