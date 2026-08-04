import { Link } from '@inertiajs/react';
import { Check, Circle } from 'lucide-react';
import { useState } from 'react';
import ConfirmIncomeDistributionTodoModal from '@/components/savings/confirm-income-distribution-todo-modal';
import FundBankBadge from '@/components/savings/fund-bank-badge';
import { Button } from '@/components/ui/button';
import { formatMoney } from '@/lib/format-money';
import { cn } from '@/lib/utils';
import type {
    IncomeDistributionTodo,
    IncomeDistributionTodoProgress,
} from '@/types/savings';

type Props = {
    periodId: string;
    teamSlug: string;
    todos: IncomeDistributionTodo[];
    progress: IncomeDistributionTodoProgress;
};

function formatCompletedDate(value: string | null): string | null {
    if (!value) {
        return null;
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return null;
    }

    return date.toLocaleDateString(undefined, {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

export default function IncomeDistributionTodos({
    periodId,
    teamSlug,
    todos,
    progress,
}: Props) {
    const [confirmTarget, setConfirmTarget] =
        useState<IncomeDistributionTodo | null>(null);

    if (todos.length === 0) {
        return null;
    }

    const completedCount = progress.totalCount - progress.pendingCount;

    return (
        <>
            <section className="mt-6 rounded-lg border p-4">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 className="font-medium">Move to your banks</h3>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Transfer each amount in your banking app first. Mark
                            complete only when the money has actually moved —
                            Kinsenas cannot verify your bank.
                        </p>
                    </div>
                    <p className="text-sm text-muted-foreground">
                        {progress.complete
                            ? 'All transfers confirmed'
                            : `${completedCount} of ${progress.totalCount} confirmed`}
                    </p>
                </div>

                <ul className="mt-4 space-y-2">
                    {todos.map((todo) => {
                        const isComplete = todo.status === 'completed';
                        const completedLabel = formatCompletedDate(
                            todo.completedAt,
                        );

                        return (
                            <li
                                key={todo.id}
                                className={cn(
                                    'flex flex-col gap-3 rounded-md border px-3 py-3 sm:flex-row sm:items-center sm:justify-between',
                                    isComplete && 'bg-muted/30',
                                )}
                            >
                                <div className="flex min-w-0 items-start gap-3">
                                    <span className="mt-0.5 shrink-0 text-muted-foreground">
                                        {isComplete ? (
                                            <Check className="size-4 text-success" />
                                        ) : (
                                            <Circle className="size-4" />
                                        )}
                                    </span>
                                    <div className="min-w-0 space-y-1">
                                        <p
                                            className={cn(
                                                'font-medium',
                                                isComplete &&
                                                    'text-muted-foreground line-through',
                                            )}
                                        >
                                            {todo.categoryName}
                                        </p>
                                        {todo.bankDisplayName ? (
                                            <FundBankBadge
                                                bankDisplayName={
                                                    todo.bankDisplayName
                                                }
                                                bankLogoUrl={todo.bankLogoUrl}
                                                layout="inline"
                                            />
                                        ) : (
                                            <p className="text-xs text-muted-foreground">
                                                No bank assigned —{' '}
                                                <Link
                                                    href={`/${teamSlug}/savings/plan`}
                                                    className="text-primary underline-offset-4 hover:underline"
                                                >
                                                    assign on your plan
                                                </Link>
                                            </p>
                                        )}
                                        {completedLabel && (
                                            <p className="text-xs text-muted-foreground">
                                                Confirmed {completedLabel}
                                            </p>
                                        )}
                                    </div>
                                </div>

                                <div className="flex shrink-0 items-center justify-between gap-3 sm:justify-end">
                                    <span
                                        className={cn(
                                            'text-sm font-semibold tabular-nums',
                                            isComplete &&
                                                'text-muted-foreground line-through',
                                        )}
                                    >
                                        {formatMoney(todo.amount)}
                                    </span>
                                    {!isComplete && (
                                        <Button
                                            type="button"
                                            size="sm"
                                            variant="outline"
                                            onClick={() =>
                                                setConfirmTarget(todo)
                                            }
                                        >
                                            Mark complete
                                        </Button>
                                    )}
                                </div>
                            </li>
                        );
                    })}
                </ul>
            </section>

            {confirmTarget !== null && (
                <ConfirmIncomeDistributionTodoModal
                    todo={confirmTarget}
                    periodId={periodId}
                    teamSlug={teamSlug}
                    open={confirmTarget !== null}
                    onOpenChange={(open) => {
                        if (!open) {
                            setConfirmTarget(null);
                        }
                    }}
                />
            )}
        </>
    );
}
