import { Head, usePage } from '@inertiajs/react';
import LandingEmotionalProblem from '@/components/marketing/landing-emotional-problem';
import LandingFinalCta from '@/components/marketing/landing-final-cta';
import LandingFooter from '@/components/marketing/landing-footer';
import LandingFormulaSection from '@/components/marketing/landing-formula-section';
import LandingHeader from '@/components/marketing/landing-header';
import LandingHero from '@/components/marketing/landing-hero';
import LandingHowItWorks from '@/components/marketing/landing-how-it-works';
import LandingOutcome from '@/components/marketing/landing-outcome';
import LandingOpenBetaBanner from '@/components/marketing/landing-open-beta-banner';
import LandingPrivacy from '@/components/marketing/landing-privacy';
import LandingTrustStrip from '@/components/marketing/landing-trust-strip';
import { dashboard } from '@/routes';

export default function Welcome() {
    const { auth, currentTeam, name } = usePage().props;
    const dashboardUrl = currentTeam ? dashboard(currentTeam.slug) : '/';
    const isAuthenticated = Boolean(auth.user);

    return (
        <>
            <Head title="Sweldo with a plan" />
            <div className="flex min-h-screen flex-col bg-background">
                <div className="sticky top-0 z-50">
                    <LandingOpenBetaBanner />
                    <LandingHeader
                        isAuthenticated={isAuthenticated}
                        dashboardUrl={dashboardUrl}
                        appName={name}
                    />
                </div>

                <main className="flex-1">
                    <LandingHero showCtas={!isAuthenticated} />
                    <LandingTrustStrip />
                    <LandingEmotionalProblem />
                    <LandingHowItWorks />
                    <LandingFormulaSection />
                    <LandingPrivacy />
                    <LandingOutcome />
                    <LandingFinalCta showCta={!isAuthenticated} />
                </main>

                <LandingFooter appName={name} />
            </div>
        </>
    );
}
