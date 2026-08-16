import { formatMoney } from '@/lib/format-money';

export function fundBucketOptionLabel(
    name: string,
    remaining: string | null,
): string {
    if (remaining === null) {
        return name;
    }

    return `${name} — ${formatMoney(remaining)} remaining`;
}

export function remainingByCategoryId(
    fundBalances: Array<{ categoryId: string; remaining: string | null }>,
): Map<string, string | null> {
    return new Map(
        fundBalances.map((balance) => [balance.categoryId, balance.remaining]),
    );
}
