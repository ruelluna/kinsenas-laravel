import { Link } from '@inertiajs/react';
import { Lock } from 'lucide-react';
import type { ReactNode } from 'react';
import { KINSENAS_HORIZONTAL_LOGO } from '@/lib/brand';
import { showsProgress } from '@/lib/survey/survey-navigation';
import type { SurveyStep } from '@/lib/survey/survey-types';
import { cn } from '@/lib/utils';
import { home } from '@/routes';

type SurveyShellProps = {
    appName: string;
    step: SurveyStep;
    questionNumber: number | null;
    progressLabel: (current: number, total: number) => string;
    privacyNote?: string;
    children: ReactNode;
};

export default function SurveyShell({
    appName,
    step,
    questionNumber,
    progressLabel,
    privacyNote,
    children,
}: SurveyShellProps) {
    const showProgress = showsProgress(step) && questionNumber !== null;
    const progressPercent = questionNumber ? (questionNumber / 10) * 100 : 0;

    return (
        <div className="relative flex min-h-dvh flex-col bg-background">
            <div
                aria-hidden
                className="pointer-events-none absolute inset-x-0 top-0 h-48 bg-linear-to-b from-primary/8 via-allocation-3/5 to-transparent"
            />

            <header className="relative border-b border-border/40 bg-background/80 backdrop-blur-md">
                <div className="mx-auto flex max-w-lg items-center justify-center px-6 py-3.5">
                    <Link href={home()} className="transition-opacity hover:opacity-80">
                        <img
                            src={KINSENAS_HORIZONTAL_LOGO}
                            alt={appName}
                            className="h-10 w-auto max-w-[min(100%,12rem)] object-contain sm:h-11"
                        />
                    </Link>
                </div>

                {showProgress && (
                    <div className="mx-auto max-w-lg px-6 pb-3">
                        <div className="mb-2 flex items-center justify-between text-xs text-muted-foreground">
                            <span>{progressLabel(questionNumber, 10)}</span>
                            <span>{Math.round(progressPercent)}%</span>
                        </div>
                        <div className="h-1.5 overflow-hidden rounded-full bg-muted/60">
                            <div
                                className="h-full rounded-full bg-primary transition-all duration-300 ease-out"
                                style={{ width: `${progressPercent}%` }}
                            />
                        </div>
                    </div>
                )}
            </header>

            <main className="relative mx-auto flex w-full max-w-lg flex-1 flex-col px-6 py-8">
                {privacyNote && (
                    <div className="mb-6 flex items-start gap-2 rounded-xl border border-border/40 bg-muted/20 px-4 py-3 text-sm text-muted-foreground">
                        <Lock className="mt-0.5 size-4 shrink-0 text-primary" aria-hidden />
                        <p className="text-pretty leading-relaxed">{privacyNote}</p>
                    </div>
                )}

                <div className={cn('flex flex-1 flex-col', !privacyNote && 'pt-2')}>{children}</div>
            </main>
        </div>
    );
}
