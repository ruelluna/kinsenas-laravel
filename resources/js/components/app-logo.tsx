import { KINSENAS_HORIZONTAL_LOGO, KINSENAS_SQUARE_LOGO } from '@/lib/brand';
import { cn } from '@/lib/utils';

type AppLogoProps = {
    className?: string;
};

export default function AppLogo({ className }: AppLogoProps) {
    return (
        <>
            <img
                src={KINSENAS_SQUARE_LOGO}
                alt="Kinsenas"
                className={cn(
                    'hidden size-8 shrink-0 object-contain group-data-[collapsible=icon]:block',
                    className,
                )}
            />
            <img
                src={KINSENAS_HORIZONTAL_LOGO}
                alt="Kinsenas"
                className={cn(
                    'h-auto max-h-11 w-full object-contain object-left group-data-[collapsible=icon]:hidden',
                    className,
                )}
            />
        </>
    );
}
