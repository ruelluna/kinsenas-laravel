import { useCallback } from 'react';
import { useSidebar } from '@/components/ui/sidebar';

export function useCloseMobileSidebar(): () => void {
    const { isMobile, setOpenMobile } = useSidebar();

    return useCallback(() => {
        if (isMobile) {
            setOpenMobile(false);
        }
    }, [isMobile, setOpenMobile]);
}
