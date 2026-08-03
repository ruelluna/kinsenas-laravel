import { useEffect, useRef } from 'react';
import {
    useMobileNav,
    type MobileNavAction,
} from '@/contexts/mobile-nav-context';

export function useRegisterMobileNavAction(
    action: MobileNavAction | null,
): void {
    const { setAction } = useMobileNav();
    const onClickRef = useRef(action?.onClick);

    onClickRef.current = action?.onClick;

    useEffect(() => {
        if (!action) {
            setAction(null);

            return;
        }

        setAction({
            label: action.label,
            ariaLabel: action.ariaLabel,
            icon: action.icon,
            disabled: action.disabled,
            onClick: () => {
                onClickRef.current?.();
            },
        });

        return () => {
            setAction(null);
        };
    }, [
        setAction,
        action?.label,
        action?.ariaLabel,
        action?.disabled,
        action?.icon,
    ]);
}
