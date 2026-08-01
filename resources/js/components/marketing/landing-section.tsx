import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

type LandingSectionProps = {
    children: ReactNode;
    className?: string;
    id?: string;
    tone?: 'default' | 'soft' | 'inset';
};

const toneClasses = {
    default: '',
    soft: 'bg-muted/30',
    inset: 'bg-gradient-to-b from-muted/20 to-background',
};

export default function LandingSection({
    children,
    className,
    id,
    tone = 'default',
}: LandingSectionProps) {
    return (
        <section
            id={id}
            className={cn(
                'px-6 py-20 lg:px-10 lg:py-28',
                toneClasses[tone],
                id && 'scroll-mt-24',
                className,
            )}
        >
            <div className="mx-auto max-w-6xl">{children}</div>
        </section>
    );
}

export function LandingSectionHeader({
    eyebrow,
    title,
    description,
    align = 'center',
    className,
}: {
    eyebrow?: string;
    title: string;
    description?: string;
    align?: 'center' | 'left';
    className?: string;
}) {
    return (
        <div
            className={cn(
                'mb-12 max-w-3xl space-y-4 lg:mb-16',
                align === 'center' && 'mx-auto text-center',
                align === 'left' && 'text-left',
                className,
            )}
        >
            {eyebrow && (
                <p className="inline-flex rounded-full bg-primary/8 px-3 py-1 text-sm font-medium text-primary">
                    {eyebrow}
                </p>
            )}
            <h2 className="text-balance text-3xl font-semibold tracking-tight text-foreground lg:text-4xl lg:leading-tight">
                {title}
            </h2>
            {description && (
                <p className="text-pretty text-lg leading-relaxed text-muted-foreground">
                    {description}
                </p>
            )}
        </div>
    );
}
