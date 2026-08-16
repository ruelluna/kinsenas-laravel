import { InstallAppMenuItem } from '@/components/pwa/install-app-menu-item';
import { usePwaInstall } from '@/contexts/pwa-install-context';

export function InstallAppSettingsSection() {
    const { canOfferInstall } = usePwaInstall();

    if (!canOfferInstall) {
        return null;
    }

    return (
        <section className="rounded-lg border p-4">
            <h2 className="text-sm font-medium">Install app</h2>
            <p className="mt-1 text-sm text-muted-foreground">
                Add Kinsenas to your home screen or desktop for quick access.
            </p>
            <div className="mt-3">
                <InstallAppMenuItem className="h-10 w-full justify-start px-3" />
            </div>
        </section>
    );
}
