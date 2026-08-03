import { InstallAppBanner } from '@kinsenas/ui';
import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { MobileBottomNav } from '@/components/mobile/mobile-bottom-nav';
import OnboardingTourHost from '@/components/onboarding/onboarding-tour-host';
import { OpenBetaBanner } from '@/components/open-beta-banner';
import { PageContent } from '@/components/page-content';
import { MobileNavProvider } from '@/contexts/mobile-nav-context';
import type { AppLayoutProps } from '@/types';

export default function AppSidebarLayout({
    children,
    breadcrumbs = [],
}: AppLayoutProps) {
    return (
        <AppShell variant="sidebar">
            <MobileNavProvider>
                <AppSidebar />
                <AppContent variant="sidebar" className="overflow-x-hidden">
                    <InstallAppBanner />
                    <AppSidebarHeader breadcrumbs={breadcrumbs} />
                    <OpenBetaBanner />
                    <PageContent className="pb-mobile-nav md:pb-6">
                        {children}
                    </PageContent>
                </AppContent>
                <MobileBottomNav />
                <OnboardingTourHost />
            </MobileNavProvider>
        </AppShell>
    );
}
