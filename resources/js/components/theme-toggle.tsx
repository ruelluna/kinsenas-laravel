import { Moon, Sun } from 'lucide-react';
import { useAppearance } from '@/hooks/use-appearance';
import { cn } from '@/lib/utils';

type ThemeToggleProps = {
    className?: string;
    'data-test'?: string;
};

export default function ThemeToggle({
    className,
    'data-test': dataTest = 'theme-toggle',
}: ThemeToggleProps) {
    const { resolvedAppearance, updateAppearance } = useAppearance();
    const isDark = resolvedAppearance === 'dark';

    return (
        <button
            type="button"
            data-test={dataTest}
            aria-label={
                isDark ? 'Switch to light theme' : 'Switch to dark theme'
            }
            onClick={() => updateAppearance(isDark ? 'light' : 'dark')}
            className={cn(
                'flex h-9 w-9 items-center justify-center rounded-full border border-border bg-surface text-muted-foreground transition-colors hover:border-primary/40 hover:text-primary',
                className,
            )}
        >
            {isDark ? (
                <Sun className="h-4 w-4" aria-hidden />
            ) : (
                <Moon className="h-4 w-4" aria-hidden />
            )}
        </button>
    );
}
