import { type ReactNode } from 'react';
import { PwaInstallProvider } from '@/contexts/pwa-install-context';

export function PwaInstallLayout({ children }: { children: ReactNode }) {
    return <PwaInstallProvider>{children}</PwaInstallProvider>;
}
