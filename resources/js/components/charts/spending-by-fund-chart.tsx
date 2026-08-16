import {
    Cell,
    Pie,
    PieChart,
    ResponsiveContainer,
    Tooltip,
} from 'recharts';
import ChartCard from '@/components/charts/chart-card';
import { allocationColor } from '@/components/charts/chart-colors';
import { formatMoney } from '@/lib/format-money';
import type { SpendingByFundPoint } from '@/types/savings';

type Props = {
    data: SpendingByFundPoint[];
};

export default function SpendingByFundChart({ data }: Props) {
    const chartData = data.map((row, index) => ({
        name: row.name,
        value: Number.parseFloat(row.total),
        color: allocationColor(index),
    }));

    return (
        <ChartCard
            title="Spending by fund"
            description="Where your confirmed spending came from."
            isEmpty={chartData.length === 0}
            emptyMessage="Record confirmed spending to see fund breakdown."
            testId="spending-by-fund-chart"
        >
            <div className="h-80">
                <ResponsiveContainer width="100%" height="100%">
                    <PieChart>
                        <Pie
                            data={chartData}
                            dataKey="value"
                            nameKey="name"
                            cx="50%"
                            cy="50%"
                            innerRadius={60}
                            outerRadius={100}
                            paddingAngle={2}
                        >
                            {chartData.map((entry) => (
                                <Cell key={entry.name} fill={entry.color} />
                            ))}
                        </Pie>
                        <Tooltip
                            formatter={(value: number) => formatMoney(value)}
                            contentStyle={{
                                backgroundColor: 'var(--card)',
                                borderColor: 'var(--border)',
                                borderRadius: '0.5rem',
                            }}
                        />
                    </PieChart>
                </ResponsiveContainer>
            </div>
            <ul className="mt-4 flex flex-wrap gap-3 text-sm">
                {chartData.map((entry) => (
                    <li key={entry.name} className="flex items-center gap-2">
                        <span
                            className="size-2.5 rounded-full"
                            style={{ backgroundColor: entry.color }}
                        />
                        <span>{entry.name}</span>
                    </li>
                ))}
            </ul>
        </ChartCard>
    );
}
