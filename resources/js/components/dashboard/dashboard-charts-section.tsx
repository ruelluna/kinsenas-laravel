import { Link } from '@inertiajs/react';
import FundUtilizationChart from '@/components/charts/fund-utilization-chart';
import SpendingTrendChart from '@/components/charts/spending-trend-chart';
import type { DashboardGraphData } from '@/types/savings';

type Props = {
    graphs: DashboardGraphData;
    reportsHref: string;
};

export default function DashboardChartsSection({
    graphs,
    reportsHref,
}: Props) {
    return (
        <section className="grid gap-6 lg:grid-cols-2">
            <FundUtilizationChart
                data={graphs.fund_utilization}
                compact
            />
            <div className="flex flex-col gap-3">
                <SpendingTrendChart
                    data={graphs.spending_over_time}
                    compact
                    title="Recent spending"
                    description="Last three months of confirmed spending."
                />
                <Link
                    href={reportsHref}
                    className="text-sm text-primary underline-offset-4 hover:underline"
                >
                    View full reports →
                </Link>
            </div>
        </section>
    );
}
