import { Link } from '@inertiajs/react';
import { useState } from 'react';
import AddFundBalanceModal from '@/components/savings/add-fund-balance-modal';
import type { ExistingFundTarget } from '@/components/savings/add-fund-balance-modal';
import FundBalanceGrid from '@/components/savings/fund-balance-grid';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import type { FundBalance } from '@/types/savings';

type Props = {
    title: string;
    description: string;
    fundBalances: FundBalance[];
    spendHref: string;
    canDrawFromFunds?: boolean;
    limit?: number;
    bordered?: boolean;
    className?: string;
};

function toExistingFundTarget(balance: FundBalance): ExistingFundTarget {
    return {
        categoryId: balance.categoryId,
        name: balance.name,
        openingBalance: balance.openingBalance,
    };
}

export default function FundBalancesSection({
    title,
    description,
    fundBalances,
    spendHref,
    canDrawFromFunds = false,
    limit,
    bordered = false,
    className,
}: Props) {
    const [fundModalOpen, setFundModalOpen] = useState(false);
    const [selectedTarget, setSelectedTarget] = useState<ExistingFundTarget | null>(null);

    if (fundBalances.length === 0) {
        return null;
    }

    const handleFund = (categoryId: string) => {
        const balance = fundBalances.find((row) => row.categoryId === categoryId) ?? null;

        setSelectedTarget(balance ? toExistingFundTarget(balance) : null);
        setFundModalOpen(true);
    };

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
                {canDrawFromFunds && (
                    <Button variant="outline" size="sm" asChild>
                        <Link href={spendHref}>Record spending</Link>
                    </Button>
                )}
            </div>
            <div className="mt-4">
                <FundBalanceGrid
                    fundBalances={fundBalances}
                    variant="compact"
                    canDrawFromFunds={canDrawFromFunds}
                    spendHref={spendHref}
                    limit={limit}
                    onFund={handleFund}
                />
            </div>
            <AddFundBalanceModal
                open={fundModalOpen}
                onOpenChange={setFundModalOpen}
                target={selectedTarget}
            />
        </>
    );

    if (bordered) {
        return <div className={cn('rounded-lg border p-4', className)}>{content}</div>;
    }

    return <section className={className}>{content}</section>;
}
