import { InstallAppSheet } from '@/components/pwa/install-app-sheet';
import { usePwaInstall } from '@/contexts/pwa-install-context';

export function PwaInstallSheetHost() {
    const { guideOpen, setGuideOpen } = usePwaInstall();

    return <InstallAppSheet open={guideOpen} onOpenChange={setGuideOpen} />;
}
