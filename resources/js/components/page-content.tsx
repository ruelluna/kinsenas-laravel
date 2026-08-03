import type { ComponentProps } from 'react';
import { cn } from '@/lib/utils';

export const pageContentPaddingX = 'px-4 md:px-6';

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
