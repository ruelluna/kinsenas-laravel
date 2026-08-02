const pesoFormatter = new Intl.NumberFormat('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

export function formatMoney(
    amount: string | number | null | undefined,
): string {
    if (amount === null || amount === undefined || amount === '') {
        return '—';
    }

    const numeric = typeof amount === 'string' ? Number(amount) : amount;

    if (Number.isNaN(numeric)) {
        return '—';
    }

    return `₱${pesoFormatter.format(numeric)}`;
}

export function formatMoneyFromCents(cents: number | null | undefined): string {
    if (cents === null || cents === undefined) {
        return '—';
    }

    return formatMoney(cents / 100);
}
