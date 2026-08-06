import { cn } from '@/lib/utils';

export default function Heading({
    title,
    description,
    variant = 'default',
    hideTitleOnMobile = false,
}: {
    title: string;
    description?: string;
    variant?: 'default' | 'small';
    hideTitleOnMobile?: boolean;
}) {
    const shouldHideTitleOnMobile =
        hideTitleOnMobile || variant === 'small';

    return (
        <header className={variant === 'small' ? '' : 'mb-8 space-y-0.5'}>
            <h2
                className={cn(
                    variant === 'small'
                        ? 'mb-0.5 text-base font-medium'
                        : 'font-space text-xl font-semibold tracking-tight',
                    shouldHideTitleOnMobile && 'max-md:sr-only',
                )}
            >
                {title}
            </h2>
            {description && (
                <p
                    className={cn(
                        'text-xs leading-relaxed text-muted-foreground md:text-sm',
                        shouldHideTitleOnMobile && 'max-md:mt-0',
                    )}
                >
                    {description}
                </p>
            )}
        </header>
    );
}
