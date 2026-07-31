import type { ImgHTMLAttributes } from 'react';
import { KINSENAS_SQUARE_LOGO } from '@/lib/brand';
import { cn } from '@/lib/utils';

export default function AppLogoIcon({
    className,
    alt = 'Kinsenas',
    ...props
}: ImgHTMLAttributes<HTMLImageElement>) {
    return (
        <img
            src={KINSENAS_SQUARE_LOGO}
            alt={alt}
            className={cn('object-contain', className)}
            {...props}
        />
    );
}
