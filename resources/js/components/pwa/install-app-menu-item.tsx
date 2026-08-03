import { Download } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { usePwaInstall } from '@/contexts/pwa-install-context';
import { cn } from '@/lib/utils';

type Props = {
    className?: string;
    onActivate?: () => void;
};

export function InstallAppMenuItem({ className, onActivate }: Props) {
    const {
        canOfferInstall,
        isIosInstall,
        canNativePrompt,
        promptInstall,
        openInstallGuide,
    } = usePwaInstall();

    if (!canOfferInstall) {
        return null;
    }

    async function handleClick(): Promise<void> {
        onActivate?.();

        if (isIosInstall || !canNativePrompt) {
            openInstallGuide();

            return;
        }

        await promptInstall();
    }

    return (
        <Button
            type="button"
            variant="ghost"
            className={cn('h-11 justify-start gap-3 px-3', className)}
            onClick={() => {
                void handleClick();
            }}
        >
            <Download className="size-5 shrink-0" />
            Install app
        </Button>
    );
}
