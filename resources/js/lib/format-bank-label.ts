import type { BankOption } from '@/types/savings';

export function formatBankOptionLabel(
    bank: Pick<BankOption, 'name' | 'accountLabel' | 'displayName'>,
): string {
    if (bank.displayName?.trim()) {
        return bank.displayName;
    }

    const label = bank.accountLabel?.trim();

    if (label) {
        return `${bank.name} — ${label}`;
    }

    return bank.name;
}

export type BankOptionGroup = {
    key: string;
    label: string;
    banks: BankOption[];
};

export function groupBankOptions(banks: BankOption[]): BankOptionGroup[] {
    const groups = new Map<string, BankOptionGroup>();
    const ungrouped: BankOption[] = [];

    for (const bank of banks) {
        if (bank.bankAccountGroupId) {
            const key = bank.bankAccountGroupId;
            const existing = groups.get(key);

            if (existing) {
                existing.banks.push(bank);
                continue;
            }

            groups.set(key, {
                key,
                label: bank.name,
                banks: [bank],
            });

            continue;
        }

        ungrouped.push(bank);
    }

    const grouped = [...groups.values()].map((group) => ({
        ...group,
        banks: [...group.banks].sort((left, right) => {
            if (left.spaceRole === 'main') {
                return -1;
            }

            if (right.spaceRole === 'main') {
                return 1;
            }

            return (left.accountLabel ?? '').localeCompare(right.accountLabel ?? '');
        }),
    }));

    if (ungrouped.length === 0) {
        return grouped;
    }

    return [
        ...grouped,
        {
            key: 'ungrouped',
            label: '',
            banks: ungrouped,
        },
    ];
}
