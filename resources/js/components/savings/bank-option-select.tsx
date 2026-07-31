import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { formatBankOptionLabel, groupBankOptions } from '@/lib/format-bank-label';
import { cn } from '@/lib/utils';
import type { BankOption } from '@/types/savings';

const NONE_VALUE = '__none__';

type Props = {
    id?: string;
    name?: string;
    banks: BankOption[];
    value: string;
    onChange?: (bankId: string) => void;
    required?: boolean;
    allowEmpty?: boolean;
    emptyLabel?: string;
    placeholder?: string;
    className?: string;
};

export function BankOptionLogo({ bank, className }: { bank: BankOption; className?: string }) {
    if (bank.logoUrl) {
        return (
            <img
                src={bank.logoUrl}
                alt=""
                className={cn('size-4 shrink-0 object-contain', className)}
            />
        );
    }

    return (
        <span
            className={cn(
                'flex size-4 shrink-0 items-center justify-center rounded bg-muted text-[10px] font-medium',
                className,
            )}
        >
            {bank.name.charAt(0)}
        </span>
    );
}

function BankOptionRow({ bank }: { bank: BankOption }) {
    return (
        <span className="flex items-center gap-2">
            <BankOptionLogo bank={bank} />
            <span className="truncate">{formatBankOptionLabel(bank)}</span>
        </span>
    );
}

function renderBankGroups(
    groups: ReturnType<typeof groupBankOptions>,
    keyPrefix: string,
) {
    return groups.map((group) => {
        const items = group.banks.map((bank) => (
            <SelectItem key={`${keyPrefix}-${bank.id}`} value={bank.id}>
                <BankOptionRow bank={bank} />
            </SelectItem>
        ));

        if (!group.label) {
            return items;
        }

        return (
            <SelectGroup key={`${keyPrefix}-${group.key}`}>
                <SelectLabel>{group.label}</SelectLabel>
                {items}
            </SelectGroup>
        );
    });
}

export default function BankOptionSelect({
    id,
    name,
    banks,
    value,
    onChange,
    required = false,
    allowEmpty = false,
    emptyLabel = 'None',
    placeholder = 'Select a bank',
    className,
}: Props) {
    const groups = groupBankOptions(banks);
    const selectValue = value === '' && allowEmpty ? NONE_VALUE : value;

    return (
        <>
            {name !== undefined && (
                <input type="hidden" name={name} value={value} required={required && value === ''} />
            )}
            <Select
                value={selectValue === '' ? undefined : selectValue}
                onValueChange={(next) => onChange?.(next === NONE_VALUE ? '' : next)}
            >
                <SelectTrigger id={id} className={cn('w-full', className)}>
                    <SelectValue placeholder={placeholder} />
                </SelectTrigger>
                <SelectContent>
                    {allowEmpty && <SelectItem value={NONE_VALUE}>{emptyLabel}</SelectItem>}
                    {renderBankGroups(groups, 'option')}
                </SelectContent>
            </Select>
        </>
    );
}
