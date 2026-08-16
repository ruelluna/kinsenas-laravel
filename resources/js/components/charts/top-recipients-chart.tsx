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
    allocationColor,
    CHART_COLORS,
} from '@/components/charts/chart-colors';
import { formatMoney } from '@/lib/format-money';
import type { TopRecipientPoint } from '@/types/savings';

type Props = {
    data: TopRecipientPoint[];
};

export default function TopRecipientsChart({ data }: Props) {
    const chartData = data.map((row, index) => ({
        name: row.name,
        total: Number.parseFloat(row.total),
        color: allocationColor(index),
    }));

    return (
        <ChartCard
            title="Top recipients"
            description="Who you paid the most in this date range."
            isEmpty={chartData.length === 0}
            emptyMessage="Assign recipients when recording spending to see this chart."
            testId="top-recipients-chart"
        >
            <div className="h-72">
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
                            stroke={CHART_COLORS.muted}
                            fontSize={12}
                            tickFormatter={(value) =>
                                formatMoney(value).replace('.00', '')
                            }
                        />
                        <YAxis
                            type="category"
                            dataKey="name"
                            width={120}
                            stroke={CHART_COLORS.muted}
                            fontSize={12}
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
                        <Bar dataKey="total" radius={[0, 4, 4, 0]}>
                            {chartData.map((entry) => (
                                <Cell key={entry.name} fill={entry.color} />
                            ))}
                        </Bar>
                    </BarChart>
                </ResponsiveContainer>
            </div>
        </ChartCard>
    );
}
