import type { ReactNode } from 'react';

type Props = {
    title: string;
    description: string;
    action?: ReactNode;
    testId?: string;
};

export default function LearnEmptyState({
    title,
    description,
    action,
    testId,
}: Props) {
    return (
        <div
            className="rounded-lg border border-dashed p-8 text-center"
            data-test={testId}
        >
            <p className="font-medium">{title}</p>
            <p className="mt-2 text-sm text-muted-foreground">{description}</p>
            {action ? <div className="mt-4 flex justify-center">{action}</div> : null}
        </div>
    );
}
