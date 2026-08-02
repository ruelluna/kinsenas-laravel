import { ArrowLeftRight, Check, Loader2, Wallet } from 'lucide-react';
import { useEffect, useState } from 'react';
import { cn } from '@/lib/utils';

type SurveyLoadingProps = {
    steps: [string, string, string];
    title: string;
    subtitle: string;
    onComplete: () => void;
};

const STEP_ICONS = [Wallet, ArrowLeftRight, Check] as const;

export default function SurveyLoading({
    steps,
    title,
    subtitle,
    onComplete,
}: SurveyLoadingProps) {
    const [activeIndex, setActiveIndex] = useState(0);

    useEffect(() => {
        const timers = [
            window.setTimeout(() => setActiveIndex(1), 1200),
            window.setTimeout(() => setActiveIndex(2), 2400),
            window.setTimeout(() => onComplete(), 3600),
        ];

        return () => {
            timers.forEach((timer) => window.clearTimeout(timer));
        };
    }, [onComplete]);

    return (
        <div className="flex flex-1 flex-col justify-center gap-8 py-6">
            <div className="space-y-2 text-center">
                <h2 className="text-xl font-semibold tracking-tight text-balance sm:text-2xl">
                    {title}
                </h2>
                <p className="text-sm text-muted-foreground">{subtitle}</p>
            </div>

            <ul className="flex flex-col gap-4">
                {steps.map((step, index) => {
                    const Icon = STEP_ICONS[index];
                    const isComplete = index < activeIndex;
                    const isActive = index === activeIndex;

                    return (
                        <li
                            key={step}
                            className={cn(
                                'flex items-center gap-3 rounded-2xl border px-4 py-3.5 transition-all duration-300',
                                isComplete && 'border-primary/20 bg-primary/5',
                                isActive &&
                                    'border-primary/30 bg-primary/8 shadow-xs',
                                !isComplete &&
                                    !isActive &&
                                    'border-border/40 bg-muted/10 opacity-60',
                            )}
                        >
                            <span
                                className={cn(
                                    'flex size-9 shrink-0 items-center justify-center rounded-full',
                                    isComplete &&
                                        'bg-primary text-primary-foreground',
                                    isActive && 'bg-primary/15 text-primary',
                                    !isComplete &&
                                        !isActive &&
                                        'bg-muted text-muted-foreground',
                                )}
                            >
                                {isComplete ? (
                                    <Check
                                        className="size-4"
                                        strokeWidth={2.5}
                                    />
                                ) : isActive ? (
                                    <Loader2 className="size-4 animate-spin" />
                                ) : (
                                    <Icon className="size-4" />
                                )}
                            </span>
                            <span className="text-sm font-medium text-pretty sm:text-base">
                                {step}
                            </span>
                        </li>
                    );
                })}
            </ul>
        </div>
    );
}
