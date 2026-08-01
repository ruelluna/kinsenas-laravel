import { Check } from 'lucide-react';
import { cn } from '@/lib/utils';

type SurveyOptionCardProps = {
    label: string;
    selected: boolean;
    onSelect: () => void;
    mode: 'single' | 'multi';
};

export default function SurveyOptionCard({
    label,
    selected,
    onSelect,
    mode,
}: SurveyOptionCardProps) {
    return (
        <button
            type="button"
            role={mode === 'single' ? 'radio' : 'checkbox'}
            aria-checked={selected}
            onClick={onSelect}
            className={cn(
                'flex w-full items-center justify-between gap-3 rounded-2xl border px-4 py-3.5 text-left text-sm leading-snug transition-[border-color,background-color,box-shadow] duration-200',
                'focus-visible:outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50',
                selected
                    ? 'border-primary bg-primary/8 shadow-xs'
                    : 'border-border/50 bg-background/80 hover:border-primary/30 hover:bg-muted/30',
            )}
        >
            <span className="text-pretty font-medium">{label}</span>
            <span
                className={cn(
                    'flex size-5 shrink-0 items-center justify-center rounded-full border transition-colors',
                    selected ? 'border-primary bg-primary text-primary-foreground' : 'border-border/60 bg-background',
                )}
                aria-hidden
            >
                {selected && <Check className="size-3" strokeWidth={3} />}
            </span>
        </button>
    );
}
