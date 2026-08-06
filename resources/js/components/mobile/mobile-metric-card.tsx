import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

export type MobileMetricRow = {
    label: string;
    value: ReactNode;
    strong?: boolean;
};

type MobileMetricCardProps = {
    title: ReactNode;
    trailing?: ReactNode;
    rows: MobileMetricRow[];
    className?: string;
};

export function MobileMetricCard({
    title,
    trailing,
    rows,
    className,
}: MobileMetricCardProps) {
    return (
        <div className={cn('rounded-lg border p-3 text-sm', className)}>
            <div className="flex items-start justify-between gap-2">
                <div className="min-w-0 font-medium leading-snug">{title}</div>
                {trailing ? (
                    <div className="shrink-0">{trailing}</div>
                ) : null}
            </div>
            <dl className="mt-3 space-y-1.5 border-t pt-3">
                {rows.map((row) => (
                    <div
                        key={row.label}
                        className="flex items-baseline justify-between gap-3 text-xs"
                    >
                        <dt className="text-muted-foreground">{row.label}</dt>
                        <dd
                            className={cn(
                                'shrink-0 text-right tabular-nums',
                                row.strong && 'font-space font-semibold',
                            )}
                        >
                            {row.value}
                        </dd>
                    </div>
                ))}
            </dl>
        </div>
    );
}

export function MobileMetricCardList({
    children,
    className,
}: {
    children: ReactNode;
    className?: string;
}) {
    return (
        <div className={cn('space-y-3', className)}>{children}</div>
    );
}
