import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { MobileBottomNav } from '@/components/mobile/mobile-bottom-nav';
import { NavigationLoadingOverlay } from '@/components/navigation/navigation-loading-overlay';
import OnboardingTourHost from '@/components/onboarding/onboarding-tour-host';
import { OpenBetaBanner } from '@/components/open-beta-banner';
import { PageContent } from '@/components/page-content';
import { InstallAppBanner } from '@/components/pwa/install-app-banner';
import { PwaInstallLayout } from '@/components/pwa/pwa-install-layout';
import { PwaInstallSheetHost } from '@/components/pwa/pwa-install-sheet-host';
import { MobileNavProvider } from '@/contexts/mobile-nav-context';
import type { AppLayoutProps } from '@/types';

export default function AppSidebarLayout({
    children,
    breadcrumbs = [],
}: AppLayoutProps) {
    return (
        <PwaInstallLayout>
            <AppShell variant="sidebar">
                <MobileNavProvider>
                    <AppSidebar />
                    <AppContent variant="sidebar" className="overflow-x-hidden">
                        <AppSidebarHeader breadcrumbs={breadcrumbs} />
                        <InstallAppBanner />
                        <OpenBetaBanner />
                        <div className="relative flex flex-1 flex-col">
                            <NavigationLoadingOverlay />
                            <PageContent className="pb-mobile-nav md:pb-6">
                                {children}
                            </PageContent>
                        </div>
                    </AppContent>
                    <MobileBottomNav />
                    <OnboardingTourHost />
                    <PwaInstallSheetHost />
                </MobileNavProvider>
            </AppShell>
        </PwaInstallLayout>
    );
}
