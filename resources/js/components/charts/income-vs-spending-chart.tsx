import {
    Bar,
    BarChart,
    CartesianGrid,
    Legend,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import ChartCard from '@/components/charts/chart-card';
import { CHART_COLORS, formatPaydayLabel } from '@/components/charts/chart-colors';
import { formatMoney } from '@/lib/format-money';
import type { IncomeVsSpendingPoint } from '@/types/savings';

type Props = {
    data: IncomeVsSpendingPoint[];
};

export default function IncomeVsSpendingChart({ data }: Props) {
    const chartData = data.map((row) => ({
        period: formatPaydayLabel(row.period),
        income: Number.parseFloat(row.income),
        spending: Number.parseFloat(row.spending),
    }));

    return (
        <ChartCard
            title="Payday in vs out"
            description="Income per payday compared to spending in that period."
            isEmpty={chartData.length === 0}
            emptyMessage="Add income periods and spending to compare paydays."
            testId="income-vs-spending-chart"
        >
            <div className="h-80">
                <ResponsiveContainer width="100%" height="100%">
                    <BarChart
                        data={chartData}
                        margin={{ top: 8, right: 12, left: 0, bottom: 0 }}
                    >
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
                            formatter={(value: number, name: string) => [
                                formatMoney(value),
                                name === 'income' ? 'Income' : 'Spending',
                            ]}
                            contentStyle={{
                                backgroundColor: 'var(--card)',
                                borderColor: 'var(--border)',
                                borderRadius: '0.5rem',
                            }}
                        />
                        <Legend />
                        <Bar
                            dataKey="income"
                            name="Income"
                            fill={CHART_COLORS.income}
                            radius={[4, 4, 0, 0]}
                        />
                        <Bar
                            dataKey="spending"
                            name="Spending"
                            fill={CHART_COLORS.spending}
                            radius={[4, 4, 0, 0]}
                        />
                    </BarChart>
                </ResponsiveContainer>
            </div>
        </ChartCard>
    );
}
