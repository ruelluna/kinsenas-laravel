import type { ComponentProps } from 'react';
import { cn } from '@/lib/utils';

export const pageContentPaddingX =
    'pl-[calc(1rem+env(safe-area-inset-left,0px))] pr-[calc(1rem+env(safe-area-inset-right,0px))] md:pl-[calc(1.5rem+env(safe-area-inset-left,0px))] md:pr-[calc(1.5rem+env(safe-area-inset-right,0px))]';

export const pageContentPaddingY = 'py-4 md:py-6';

export const pageContentClasses = cn(
    'flex flex-1 flex-col gap-3 md:gap-4',
    pageContentPaddingX,
    pageContentPaddingY,
);

type PageContentProps = ComponentProps<'div'>;

export function PageContent({
    className,
    children,
    ...props
}: PageContentProps) {
    return (
        <div className={cn(pageContentClasses, className)} {...props}>
            {children}
        </div>
    );
}
