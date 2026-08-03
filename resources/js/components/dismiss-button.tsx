import { X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

type DismissButtonProps = {
    onDismiss: () => void;
    label?: string;
    className?: string;
};

export function DismissButton({
    onDismiss,
    label = 'Dismiss',
    className,
}: DismissButtonProps) {
    return (
        <Button
            type="button"
            variant="ghost"
            size="icon"
            className={cn('size-8 shrink-0', className)}
            onClick={onDismiss}
            aria-label={label}
        >
            <X className="size-4" />
        </Button>
    );
}
