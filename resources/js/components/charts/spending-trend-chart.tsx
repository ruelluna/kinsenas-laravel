import {
    Area,
    AreaChart,
    CartesianGrid,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import ChartCard from '@/components/charts/chart-card';
import {
    CHART_COLORS,
    formatChartPeriod,
} from '@/components/charts/chart-colors';
import { formatMoney } from '@/lib/format-money';
import type { SpendingOverTimePoint } from '@/types/savings';

type Props = {
    data: SpendingOverTimePoint[];
    compact?: boolean;
    title?: string;
    description?: string;
    testId?: string;
};

export default function SpendingTrendChart({
    data,
    compact = false,
    title = 'Spending trend',
    description = 'Confirmed spending grouped by month.',
    testId = 'spending-trend-chart',
}: Props) {
    const chartData = data.map((row) => ({
        period: formatChartPeriod(row.period),
        total: Number.parseFloat(row.total),
    }));

    return (
        <ChartCard
            title={title}
            description={description}
            isEmpty={chartData.length === 0}
            emptyMessage="Record confirmed spending to see trends."
            compact={compact}
            testId={testId}
        >
            <div className={compact ? 'h-48' : 'h-72'}>
                <ResponsiveContainer width="100%" height="100%">
                    <AreaChart
                        data={chartData}
                        margin={{ top: 8, right: 12, left: 0, bottom: 0 }}
                    >
                        <defs>
                            <linearGradient
                                id="spendingTrendFill"
                                x1="0"
                                y1="0"
                                x2="0"
                                y2="1"
                            >
                                <stop
                                    offset="5%"
                                    stopColor={CHART_COLORS.trend}
                                    stopOpacity={0.35}
                                />
                                <stop
                                    offset="95%"
                                    stopColor={CHART_COLORS.trend}
                                    stopOpacity={0.05}
                                />
                            </linearGradient>
                        </defs>
                        <CartesianGrid stroke={CHART_COLORS.grid} />
                        <XAxis
                            dataKey="period"
                            stroke={CHART_COLORS.muted}
                            fontSize={12}
                        />
                        <YAxis
                            stroke={CHART_COLORS.muted}
                            fontSize={12}
                            tickFormatter={(value) =>
                                formatMoney(value).replace('.00', '')
                            }
                        />
                        <Tooltip
                            formatter={(value: number) => [
                                formatMoney(value),
                                'Spent',
                            ]}
                            contentStyle={{
                                backgroundColor: 'var(--card)',
                                borderColor: 'var(--border)',
                                borderRadius: '0.5rem',
                            }}
                        />
                        <Area
                            type="monotone"
                            dataKey="total"
                            stroke={CHART_COLORS.trend}
                            fill="url(#spendingTrendFill)"
                            strokeWidth={2}
                        />
                    </AreaChart>
                </ResponsiveContainer>
            </div>
        </ChartCard>
    );
}
