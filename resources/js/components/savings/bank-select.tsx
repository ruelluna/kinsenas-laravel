import BankOptionSelect, { BankOptionLogo } from '@/components/savings/bank-option-select';
import type { BankOption, CategoryBankMap } from '@/types/savings';

type Props = {
    banks: BankOption[];
    categoryBankMap: CategoryBankMap;
    categoryId: string;
    name?: string;
    id?: string;
    required?: boolean;
    value?: string;
    onChange?: (bankId: string) => void;
};

export function banksForCategory(
    banks: BankOption[],
    categoryBankMap: CategoryBankMap,
    categoryId: string,
): BankOption[] {
    const assigned = categoryBankMap[categoryId] ?? null;

    if (assigned === null || assigned === '') {
        return banks;
    }

    return banks.filter((bank) => bank.id === assigned);
}

export default function BankSelect({
    banks,
    categoryBankMap,
    categoryId,
    name = 'bank_id',
    id = 'bank_id',
    required = false,
    value = '',
    onChange,
}: Props) {
    const options = banksForCategory(banks, categoryBankMap, categoryId);

    if (required && options.length === 0) {
        return (
            <select
                id={id}
                name={name}
                className="border-input h-9 w-full rounded-md border px-3 text-sm"
                disabled
            >
                <option value="">No bank assigned</option>
            </select>
        );
    }

    return (
        <BankOptionSelect
            id={id}
            name={name}
            banks={options}
            value={value}
            onChange={onChange}
            required={required}
            allowEmpty={!required}
            placeholder={required ? 'Select a bank' : 'None'}
        />
    );
}

export function BankLogo({ logoUrl, name }: { logoUrl?: string | null; name: string }) {
    return (
        <BankOptionLogo
            bank={{ id: name, name, logoUrl: logoUrl ?? null }}
            className="size-8 text-xs"
        />
    );
}
