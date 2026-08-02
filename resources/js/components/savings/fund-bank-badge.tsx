import { BankOptionLogo } from '@/components/savings/bank-option-select';
import { cn } from '@/lib/utils';

type Props = {
    bankDisplayName: string;
    bankLogoUrl: string | null;
    layout?: 'corner' | 'inline';
    className?: string;
};

export default function FundBankBadge({
    bankDisplayName,
    bankLogoUrl,
    layout = 'corner',
    className,
}: Props) {
    if (!bankDisplayName.trim()) {
        return null;
    }

    const logo = (
        <BankOptionLogo
            bank={{
                id: bankDisplayName,
                name: bankDisplayName,
                logoUrl: bankLogoUrl,
            }}
            className={
                layout === 'inline'
                    ? 'size-5 text-[10px]'
                    : 'size-6 text-[10px]'
            }
        />
    );

    if (layout === 'inline') {
        return (
            <span
                className={cn('flex shrink-0 items-center gap-1.5', className)}
            >
                {logo}
                <span
                    className="max-w-[8rem] truncate text-xs text-muted-foreground"
                    title={bankDisplayName}
                >
                    {bankDisplayName}
                </span>
            </span>
        );
    }

    return (
        <div
            className={cn(
                'flex shrink-0 flex-col items-end gap-0.5',
                className,
            )}
        >
            {logo}
            <span
                className="max-w-[7rem] truncate text-xs text-muted-foreground"
                title={bankDisplayName}
            >
                {bankDisplayName}
            </span>
        </div>
    );
}
