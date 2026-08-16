import FundBankBadge from '@/components/savings/fund-bank-badge';
import { Badge } from '@/components/ui/badge';
import { categoryTitleWithAllocation } from '@/lib/category-allocation-label';
import type { FundBalance } from '@/types/savings';

type Props = Pick<
    FundBalance,
    | 'name'
    | 'hint'
    | 'isDefault'
    | 'bankId'
    | 'bankDisplayName'
    | 'bankLogoUrl'
    | 'allocationType'
    | 'percentage'
    | 'deductionMode'
    | 'deductionValue'
> & {
    showAllocationPercent?: boolean;
};

export default function FundCardHeader({
    name,
    hint,
    isDefault,
    bankId,
    bankDisplayName,
    bankLogoUrl,
    allocationType,
    percentage,
    deductionMode,
    deductionValue,
    showAllocationPercent = false,
}: Props) {
    const title = showAllocationPercent
        ? categoryTitleWithAllocation(name, {
              allocationType,
              percentage,
              deductionMode,
              deductionValue,
          })
        : name;

    return (
        <div className="flex items-start justify-between gap-2">
            <div className="min-w-0">
                <p className="font-medium">{title}</p>
                {hint && (
                    <p className="mt-0.5 text-xs text-muted-foreground">
                        {hint}
                    </p>
                )}
            </div>
            <div className="flex shrink-0 flex-col items-end gap-1">
                {bankId && bankDisplayName && (
                    <FundBankBadge
                        bankDisplayName={bankDisplayName}
                        bankLogoUrl={bankLogoUrl}
                        layout="corner"
                    />
                )}
                {isDefault && <Badge variant="secondary">Default</Badge>}
            </div>
        </div>
    );
}
