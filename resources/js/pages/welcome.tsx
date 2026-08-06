import { Head, usePage } from '@inertiajs/react';
import LandingBanks from '@/components/marketing/landing-banks';
import LandingEmotionalProblem from '@/components/marketing/landing-emotional-problem';
import LandingFilipinoSpending from '@/components/marketing/landing-filipino-spending';
import LandingFinalCta from '@/components/marketing/landing-final-cta';
import LandingFormulaSection from '@/components/marketing/landing-formula-section';
import LandingHeader from '@/components/marketing/landing-header';
import LandingHero from '@/components/marketing/landing-hero';
import LandingHowItWorks from '@/components/marketing/landing-how-it-works';
import LandingPrivacy from '@/components/marketing/landing-privacy';
import { dashboard } from '@/routes';

export default function Welcome() {
    const { auth, currentTeam } = usePage().props;
    const dashboardUrl = currentTeam ? dashboard(currentTeam.slug) : '/';
    const isAuthenticated = Boolean(auth.user);

    return (
        <>
            <Head title="Sweldo with a plan" />
            <div className="landing-marketing min-h-screen bg-midnight font-dm text-foreground selection:bg-primary/30">
                <LandingHeader
                    isAuthenticated={isAuthenticated}
                    dashboardUrl={dashboardUrl}
                />

                <main>
                    <LandingHero showCtas={!isAuthenticated} />
                    <LandingEmotionalProblem />
                    <LandingFilipinoSpending />
                    <LandingBanks />
                    <LandingHowItWorks />
                    <LandingFormulaSection />
                    <LandingPrivacy />
                    <LandingFinalCta showCta={!isAuthenticated} />
                </main>
            </div>
        </>
    );
}
