import { Head, Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import PendingActionsPanel from '@/components/dashboard/pending-actions-panel';
import RecentActivityFeed from '@/components/dashboard/recent-activity-feed';
import SetupChecklist from '@/components/dashboard/setup-checklist';
import SummaryStatCards from '@/components/dashboard/summary-stat-cards';
import PendingInvitationsModal from '@/components/pending-invitations-modal';
import FundBalanceGrid from '@/components/savings/fund-balance-grid';
import { Button } from '@/components/ui/button';
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
    features,
    quickLinks,
}: Props) {
    const [showInvitations, setShowInvitations] = useState(
        pendingInvitations.length > 0,
    );
    const recoveryKey = usePage<SharedData & { registrationRecoveryKey?: string | null }>().props
        .registrationRecoveryKey;

    const showFinancialSections = setup.hasPlan && setup.hasLockedIncome;

    return (
        <>
            <Head title="Dashboard" />
            {recoveryKey && (
                <div className="rounded-lg border border-warning/50 bg-warning/10 p-4 text-sm">
                    <p className="font-medium">Save your recovery key</p>
                    <p className="mt-1 text-muted-foreground">
                        Store this somewhere safe. You need it if you reset your password.
                    </p>
                    <code className="mt-2 block break-all rounded bg-muted p-2 text-xs">{recoveryKey}</code>
                </div>
            )}
            <PendingInvitationsModal
                invitations={pendingInvitations}
                open={pendingInvitations.length > 0 && showInvitations}
                onOpenChange={setShowInvitations}
            />

            <div className="flex flex-col gap-6">
                <SetupChecklist setup={setup} />

                <SummaryStatCards setup={setup} summary={summary} quickLinks={quickLinks} />

                {showFinancialSections && (
                    <>
                        <section>
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <h2 className="font-medium">{plan?.name ?? 'Fund balances'}</h2>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        Running totals from locked income minus transfers and spending.
                                    </p>
                                </div>
                                <Button variant="outline" size="sm" asChild>
                                    <Link href={quickLinks.spending}>Record spending</Link>
                                </Button>
                            </div>
                            <div className="mt-4">
                                <FundBalanceGrid
                                    fundBalances={fundBalances}
                                    variant="compact"
                                    hasLockedIncome={plan?.hasLockedIncome ?? false}
                                    spendHref={quickLinks.spending}
                                />
                            </div>
                            <div className="mt-4 flex flex-wrap gap-4 text-sm text-muted-foreground">
                                <Link href={quickLinks.plan} className="text-primary underline-offset-4 hover:underline">
                                    Savings Plan
                                </Link>
                                <Link href={quickLinks.income} className="text-primary underline-offset-4 hover:underline">
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
                        </section>

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
                            <Button variant="outline" size="sm" asChild data-tour="add-bank">
                                <Link href={quickLinks.banks}>Add bank</Link>
                            </Button>
                        )}
                        {setup.hasPlan && (
                            <>
                                <Button variant="outline" size="sm" asChild>
                                    <Link href={quickLinks.income}>Add income</Link>
                                </Button>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    asChild={setup.hasLockedIncome}
                                    disabled={!setup.hasLockedIncome}
                                >
                                    {setup.hasLockedIncome ? (
                                        <Link href={quickLinks.spending}>Add spending</Link>
                                    ) : (
                                        <span>Add spending</span>
                                    )}
                                </Button>
                                {features.transfers && (
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        asChild={setup.hasLockedIncome}
                                        disabled={!setup.hasLockedIncome}
                                    >
                                        {setup.hasLockedIncome ? (
                                            <Link href={quickLinks.transfers}>Transfer funds</Link>
                                        ) : (
                                            <span>Transfer funds</span>
                                        )}
                                    </Button>
                                )}
                                {setup.hasBank && (
                                    <Button variant="outline" size="sm" asChild>
                                        <Link href={quickLinks.banks}>Add bank</Link>
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
