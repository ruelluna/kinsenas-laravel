export const ALLOCATION_CHART_COLORS = [
    'var(--allocation-1)',
    'var(--allocation-2)',
    'var(--allocation-3)',
    'var(--allocation-4)',
    'var(--allocation-5)',
    'var(--allocation-6)',
    'var(--allocation-7)',
] as const;

export function allocationColor(index: number): string {
    return ALLOCATION_CHART_COLORS[index % ALLOCATION_CHART_COLORS.length];
}

export function utilizationBarColor(percentUsed: number): string {
    if (percentUsed >= 90) {
        return 'var(--destructive)';
    }

    if (percentUsed >= 70) {
        return 'var(--warning)';
    }

    return 'var(--success)';
}

export const CHART_COLORS = {
    income: 'var(--success)',
    spending: 'var(--destructive)',
    trend: 'var(--primary)',
    muted: 'var(--muted-foreground)',
    grid: 'var(--border)',
} as const;

export function formatChartPeriod(period: string): string {
    const [year, month] = period.split('-');

    if (!year || !month) {
        return period;
    }

    const date = new Date(Number(year), Number(month) - 1, 1);

    return date.toLocaleDateString(undefined, {
        month: 'short',
        year: '2-digit',
    });
}

export function formatPaydayLabel(period: string): string {
    const date = new Date(period);

    if (Number.isNaN(date.getTime())) {
        return period;
    }

    return date.toLocaleDateString(undefined, {
        month: 'short',
        day: 'numeric',
    });
}
