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
    const assigned = categoryBankMap[categoryId] ?? [];

    if (assigned.length === 0) {
        return banks;
    }

    return banks.filter((bank) => assigned.includes(bank.id));
}

export default function BankSelect({
    banks,
    categoryBankMap,
    categoryId,
    name = 'bank_id',
    id = 'bank_id',
    required = false,
    value,
    onChange,
}: Props) {
    const options = banksForCategory(banks, categoryBankMap, categoryId);

    return (
        <select
            id={id}
            name={name}
            className="border-input h-9 w-full rounded-md border px-3 text-sm"
            value={value}
            onChange={onChange ? (event) => onChange(event.target.value) : undefined}
            required={required}
        >
            {!required && <option value="">None</option>}
            {required && options.length === 0 && <option value="">No banks assigned</option>}
            {options.map((bank) => (
                <option key={bank.id} value={bank.id}>
                    {bank.name}
                </option>
            ))}
        </select>
    );
}

export function BankLogo({ logoUrl, name }: { logoUrl?: string | null; name: string }) {
    if (!logoUrl) {
        return (
            <span className="flex size-8 shrink-0 items-center justify-center rounded bg-muted text-xs font-medium">
                {name.charAt(0)}
            </span>
        );
    }

    return <img src={logoUrl} alt="" className="size-8 shrink-0 object-contain" />;
}
