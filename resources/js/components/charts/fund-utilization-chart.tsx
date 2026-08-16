import {
    Bar,
    BarChart,
    CartesianGrid,
    Cell,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import ChartCard from '@/components/charts/chart-card';
import {
    CHART_COLORS,
    utilizationBarColor,
} from '@/components/charts/chart-colors';
import type { FundUtilizationPoint } from '@/types/savings';

type Props = {
    data: FundUtilizationPoint[];
    compact?: boolean;
    testId?: string;
};

export default function FundUtilizationChart({
    data,
    compact = false,
    testId = 'fund-utilization-chart',
}: Props) {
    const chartData = data.map((row) => ({
        name: row.name,
        percentUsed: row.percent_used,
    }));

    return (
        <ChartCard
            title="Fund utilization"
            description="How much of each fund bucket's spendable pool you have used."
            isEmpty={chartData.length === 0}
            emptyMessage="Add income or existing savings and record spending to see fund utilization."
            compact={compact}
            testId={testId}
        >
            <div className={compact ? 'h-56' : 'h-72'}>
                <ResponsiveContainer width="100%" height="100%">
                    <BarChart
                        data={chartData}
                        layout="vertical"
                        margin={{ top: 4, right: 12, left: 8, bottom: 4 }}
                    >
                        <CartesianGrid
                            stroke={CHART_COLORS.grid}
                            horizontal={false}
                        />
                        <XAxis
                            type="number"
                            domain={[0, 100]}
                            tickFormatter={(value) => `${value}%`}
                            stroke={CHART_COLORS.muted}
                            fontSize={12}
                        />
                        <YAxis
                            type="category"
                            dataKey="name"
                            width={compact ? 96 : 120}
                            stroke={CHART_COLORS.muted}
                            fontSize={12}
                        />
                        <Tooltip
                            formatter={(value: number) => [`${value}%`, 'Used']}
                            contentStyle={{
                                backgroundColor: 'var(--card)',
                                borderColor: 'var(--border)',
                                borderRadius: '0.5rem',
                            }}
                        />
                        <Bar dataKey="percentUsed" radius={[0, 4, 4, 0]}>
                            {chartData.map((entry) => (
                                <Cell
                                    key={entry.name}
                                    fill={utilizationBarColor(entry.percentUsed)}
                                />
                            ))}
                        </Bar>
                    </BarChart>
                </ResponsiveContainer>
            </div>
        </ChartCard>
    );
}
