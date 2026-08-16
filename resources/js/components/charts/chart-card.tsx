import type { ReactNode } from 'react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { cn } from '@/lib/utils';

type ChartCardProps = {
    title: string;
    description?: string;
    emptyMessage?: string;
    isEmpty?: boolean;
    children: ReactNode;
    className?: string;
    compact?: boolean;
    testId?: string;
};

export default function ChartCard({
    title,
    description,
    emptyMessage = 'No data to display yet.',
    isEmpty = false,
    children,
    className,
    compact = false,
    testId,
}: ChartCardProps) {
    return (
        <Card
            className={cn(compact && 'py-4', className)}
            data-test={testId}
        >
            <CardHeader className={cn(compact && 'px-4')}>
                <CardTitle className={cn(compact && 'text-base')}>
                    {title}
                </CardTitle>
                {description && (
                    <CardDescription>{description}</CardDescription>
                )}
            </CardHeader>
            <CardContent className={cn(compact && 'px-4')}>
                {isEmpty ? (
                    <p className="rounded-lg border border-dashed px-4 py-8 text-center text-sm text-muted-foreground">
                        {emptyMessage}
                    </p>
                ) : (
                    children
                )}
            </CardContent>
        </Card>
    );
}
