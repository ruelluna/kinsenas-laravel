import { useCallback } from 'react';
import { useOptionalSidebar } from '@/components/ui/sidebar';

export function useCloseMobileSidebar(): () => void {
    const sidebar = useOptionalSidebar();

    return useCallback(() => {
        if (sidebar?.isMobile) {
            sidebar.setOpenMobile(false);
        }
    }, [sidebar]);
}
