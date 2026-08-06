import type { ReactNode } from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { cn } from '@/lib/utils';

export type Column<T> = {
    key: string;
    header: string;
    render: (item: T) => ReactNode;
    width?: number | string;
};

export type ResponsiveDataViewProps<T> = {
    data: T[];
    columns: Column<T>[];
    keyExtractor: (item: T) => string;
    emptyMessage?: string;
    isCompact?: boolean;
};

export function ResponsiveDataView<T>({
    data,
    columns,
    keyExtractor,
    emptyMessage = 'No records found.',
    isCompact = false,
}: ResponsiveDataViewProps<T>) {
    if (data.length === 0) {
        return (
            <p className="py-6 text-center text-sm text-muted-foreground">
                {emptyMessage}
            </p>
        );
    }

    if (isCompact) {
        return (
            <div className="flex flex-col gap-3">
                {data.map((item) => (
                    <Card key={keyExtractor(item)} className="py-4 shadow-sm">
                        <CardContent className="flex flex-col gap-2 px-4">
                            {columns.map((column) => (
                                <div
                                    key={column.key}
                                    className="flex items-start justify-between gap-2"
                                >
                                    <span className="shrink-0 text-[13px] text-muted-foreground">
                                        {column.header}
                                    </span>
                                    <div className="min-w-0 flex-1 text-right">
                                        {column.render(item)}
                                    </div>
                                </div>
                            ))}
                        </CardContent>
                    </Card>
                ))}
            </div>
        );
    }

    return (
        <div className="overflow-x-auto">
            <div className="min-w-full">
                <div className="flex border-b border-border bg-secondary px-3 py-2">
                    {columns.map((column) => (
                        <div
                            key={column.key}
                            className={cn(
                                'pr-3 text-[13px] font-semibold',
                                typeof column.width === 'number'
                                    ? undefined
                                    : 'min-w-[8.75rem]',
                            )}
                            style={
                                typeof column.width === 'number'
                                    ? { width: column.width }
                                    : column.width
                                      ? { width: column.width }
                                      : { width: '8.75rem' }
                            }
                        >
                            {column.header}
                        </div>
                    ))}
                </div>
                {data.map((item) => (
                    <div
                        key={keyExtractor(item)}
                        className="flex border-b border-border px-3 py-2.5"
                    >
                        {columns.map((column) => (
                            <div
                                key={column.key}
                                className="pr-3"
                                style={
                                    typeof column.width === 'number'
                                        ? { width: column.width }
                                        : column.width
                                          ? { width: column.width }
                                          : { width: '8.75rem' }
                                }
                            >
                                {column.render(item)}
                            </div>
                        ))}
                    </div>
                ))}
            </div>
        </div>
    );
}
