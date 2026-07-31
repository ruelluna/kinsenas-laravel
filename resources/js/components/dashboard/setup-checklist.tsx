import { Link } from '@inertiajs/react';
import { Check, Circle } from 'lucide-react';
import { cn } from '@/lib/utils';
import type { DashboardSetup } from '@/types/dashboard';

type Props = {
    setup: DashboardSetup;
};

export default function SetupChecklist({ setup }: Props) {
    if (setup.complete) {
        return (
            <div className="flex items-center gap-2 rounded-lg border border-success/30 bg-success/5 px-4 py-3 text-sm">
                <Check className="size-4 shrink-0 text-success" />
                <span className="font-medium">All set</span>
                <span className="text-muted-foreground">Your savings workspace is ready.</span>
            </div>
        );
    }

    const nextStep = setup.steps.find((step) => !step.complete);

    return (
        <div className="rounded-lg border p-4">
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 className="font-medium">Get started</h2>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Complete these steps to track your savings plan.
                    </p>
                </div>
                {nextStep && (
                    <Link
                        href={nextStep.href}
                        className="inline-flex h-9 items-center justify-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                    >
                        Continue setup
                    </Link>
                )}
            </div>
            <ol className="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
                {setup.steps.map((step, index) => (
                    <li key={step.key}>
                        <Link
                            href={step.href}
                            className={cn(
                                'flex items-start gap-2 rounded-md border px-3 py-2 text-sm transition-colors hover:bg-muted/50',
                                step.complete && 'border-success/30 bg-success/5',
                                !step.complete && nextStep?.key === step.key && 'ring-2 ring-primary/20',
                            )}
                        >
                            {step.complete ? (
                                <Check className="mt-0.5 size-4 shrink-0 text-success" />
                            ) : (
                                <Circle className="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                            )}
                            <span>
                                <span className="block text-xs text-muted-foreground">Step {index + 1}</span>
                                <span className={cn('font-medium', step.complete && 'text-muted-foreground')}>
                                    {step.label}
                                </span>
                            </span>
                        </Link>
                    </li>
                ))}
            </ol>
        </div>
    );
}
