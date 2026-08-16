import { Head, Link, usePage } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import PendingActionsPanel from '@/components/dashboard/pending-actions-panel';
import DashboardChartsSection from '@/components/dashboard/dashboard-charts-section';
import LearnHighlightsCard from '@/components/dashboard/learn-highlights-card';
import RecentActivityFeed from '@/components/dashboard/recent-activity-feed';
import SetupChecklist from '@/components/dashboard/setup-checklist';
import SummaryStatCards from '@/components/dashboard/summary-stat-cards';
import PendingInvitationsModal from '@/components/pending-invitations-modal';
import AddSpendingModal from '@/components/savings/add-spending-modal';
import FundBalancesSection from '@/components/savings/fund-balances-section';
import { Button } from '@/components/ui/button';
import { useRegisterMobileNavAction } from '@/hooks/use-register-mobile-nav-action';
import { markPwaEngagementFromPlan } from '@/lib/pwa-install';
import { requestOnboardingTourAutoStart } from '@/lib/onboarding-tour/storage';
import { dashboard } from '@/routes';
import type { DashboardInvitation, SharedData } from '@/types';
import type { DashboardPageProps } from '@/types/dashboard';

type Props = DashboardPageProps & {
    pendingInvitations?: DashboardInvitation[];
};

export default function Dashboard({
    pendingInvitations = [],
    setup,
    plan,
    summary,
    fundBalances,
    pendingActions,
    recentActivity,
    dashboardGraphs,
    features,
    quickLinks,
    quickSpend,
    learnHighlights = [],
}: Props) {
    const [showInvitations, setShowInvitations] = useState(
        pendingInvitations.length > 0,
    );
    const [addSpendOpen, setAddSpendOpen] = useState(false);
    const page = usePage<
        SharedData & { registrationRecoveryKey?: string | null }
    >();
    const recoveryKey = page.props.registrationRecoveryKey;
    const teamId = page.props.currentTeam?.id;

    const showFinancialSections = setup.hasPlan;

    useEffect(() => {
        if (setup.hasPlan) {
            markPwaEngagementFromPlan();
        }
    }, [setup.hasPlan]);

    useEffect(() => {
        if (!page.props.onboardingTourEnabled) {
            return;
        }

        if (teamId && !setup.complete) {
            requestOnboardingTourAutoStart(teamId);
        }
    }, [page.props.onboardingTourEnabled, teamId, setup.complete]);

    const mobileNavAction = useMemo(
        () =>
            quickSpend && setup.canDrawFromFunds
                ? {
                      label: 'Add spending',
                      ariaLabel: 'Add spending',
                      icon: Plus,
                      onClick: () => setAddSpendOpen(true),
                  }
                : null,
        [quickSpend, setup.canDrawFromFunds],
    );

    useRegisterMobileNavAction(mobileNavAction);

    return (
        <>
            <Head title="Dashboard" />
            {recoveryKey && (
                <div className="rounded-lg border border-warning/50 bg-warning/10 p-4 text-sm">
                    <p className="font-medium">Save your recovery key</p>
                    <p className="mt-1 text-muted-foreground">
                        Store this somewhere safe. You need it if you reset your
                        password.
                    </p>
                    <code className="mt-2 block rounded bg-muted p-2 text-xs break-all">
                        {recoveryKey}
                    </code>
                </div>
            )}
            <PendingInvitationsModal
                invitations={pendingInvitations}
                open={pendingInvitations.length > 0 && showInvitations}
                onOpenChange={setShowInvitations}
            />

            {quickSpend && setup.canDrawFromFunds && (
                <AddSpendingModal
                    open={addSpendOpen}
                    onOpenChange={setAddSpendOpen}
                    presetCategoryId={quickSpend.defaultCategoryId}
                    defaultCategoryId={quickSpend.defaultCategoryId}
                    categories={quickSpend.categories}
                    fundBalances={fundBalances}
                    recipients={quickSpend.recipients}
                />
            )}

            <div className="flex flex-col gap-4 md:gap-6">
                <SetupChecklist setup={setup} />

                <LearnHighlightsCard posts={learnHighlights} />

                <SummaryStatCards setup={setup} summary={summary} />

                {showFinancialSections && dashboardGraphs && setup.hasSpending && (
                    <DashboardChartsSection
                        graphs={dashboardGraphs}
                        reportsHref={quickLinks.reports}
                    />
                )}

                {showFinancialSections && (
                    <>
                        <FundBalancesSection
                            title={plan?.name ?? 'Fund balances'}
                            description={
                                setup.canDrawFromFunds
                                    ? 'Running totals from existing funds and income minus transfers and spending.'
                                    : 'Add income or existing savings to any fund bucket to start tracking balances.'
                            }
                            fundBalances={fundBalances}
                            spendHref={quickLinks.spending}
                            canDrawFromFunds={plan?.canDrawFromFunds ?? false}
                            showAllocationPercent
                            fundDetailHref={(categoryId) =>
                                `/${page.props.currentTeam?.slug}/savings/funds/${categoryId}`
                            }
                        />
                        <div className="mt-4 flex flex-wrap gap-4 text-sm text-muted-foreground">
                            <Link
                                href={quickLinks.plan}
                                className="text-primary underline-offset-4 hover:underline"
                            >
                                Savings Plan
                            </Link>
                            <Link
                                href={quickLinks.income}
                                className="text-primary underline-offset-4 hover:underline"
                            >
                                Income
                            </Link>
                            {features.reports && (
                                <Link
                                    href={quickLinks.reports}
                                    className="text-primary underline-offset-4 hover:underline"
                                >
                                    Reports
                                </Link>
                            )}
                        </div>

                        <div className="grid gap-6 lg:grid-cols-2">
                            <PendingActionsPanel
                                pendingActions={pendingActions}
                                quickLinks={quickLinks}
                                features={features}
                            />
                            <RecentActivityFeed
                                recentActivity={recentActivity}
                                quickLinks={quickLinks}
                                features={features}
                            />
                        </div>
                    </>
                )}

                {(setup.hasPlan || !setup.hasBank) && (
                    <section className="flex flex-wrap gap-2">
                        {!setup.hasBank && (
                            <Button
                                variant="outline"
                                size="sm"
                                asChild
                                data-tour="add-bank"
                            >
                                <Link href={quickLinks.banks}>Add bank</Link>
                            </Button>
                        )}
                        {setup.hasPlan && (
                            <>
                                <Button variant="outline" size="sm" asChild>
                                    <Link href={quickLinks.income}>
                                        Add income
                                    </Link>
                                </Button>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    asChild={setup.canDrawFromFunds}
                                    disabled={!setup.canDrawFromFunds}
                                >
                                    {setup.canDrawFromFunds ? (
                                        <Link href={quickLinks.spending}>
                                            Add spending
                                        </Link>
                                    ) : (
                                        <span>Add spending</span>
                                    )}
                                </Button>
                                {features.transfers && (
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        asChild={setup.canDrawFromFunds}
                                        disabled={!setup.canDrawFromFunds}
                                    >
                                        {setup.canDrawFromFunds ? (
                                            <Link href={quickLinks.transfers}>
                                                Transfer funds
                                            </Link>
                                        ) : (
                                            <span>Transfer funds</span>
                                        )}
                                    </Button>
                                )}
                                {setup.hasBank && (
                                    <Button variant="outline" size="sm" asChild>
                                        <Link href={quickLinks.banks}>
                                            Add bank
                                        </Link>
                                    </Button>
                                )}
                            </>
                        )}
                    </section>
                )}
            </div>
        </>
    );
}

Dashboard.layout = (props: { currentTeam?: { slug: string } | null }) => ({
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: props.currentTeam ? dashboard(props.currentTeam.slug) : '/',
        },
    ],
});
