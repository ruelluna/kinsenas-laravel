import { Link } from '@inertiajs/react';
import { ArrowRightLeft, ShoppingBag } from 'lucide-react';
import { formatMoney } from '@/lib/format-money';
import type {
    DashboardActivityItem,
    DashboardFeatures,
    DashboardQuickLinks,
} from '@/types/dashboard';

type Props = {
    recentActivity: DashboardActivityItem[];
    quickLinks: DashboardQuickLinks;
    features: DashboardFeatures;
};

export default function RecentActivityFeed({
    recentActivity,
    quickLinks,
    features,
}: Props) {
    return (
        <div className="rounded-lg border p-4">
            <div className="flex items-center justify-between gap-3">
                <h3 className="font-medium">Recent activity</h3>
            </div>
            <div className="mt-3 space-y-3">
                {recentActivity.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        No activity recorded yet.
                    </p>
                ) : (
                    recentActivity.map((item) => (
                        <div
                            key={item.id}
                            className="flex items-start gap-3 rounded-md border p-3 text-sm"
                        >
                            {item.type === 'transfer' ? (
                                <ArrowRightLeft className="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                            ) : (
                                <ShoppingBag className="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                            )}
                            <div>
                                <p className="font-medium">
                                    {formatMoney(item.amount)}
                                    {item.description
                                        ? ` · ${item.description}`
                                        : ''}
                                </p>
                                <p className="text-muted-foreground">
                                    {item.label} · {item.date}
                                </p>
                            </div>
                        </div>
                    ))
                )}
            </div>
            <div className="mt-3 flex flex-wrap gap-4 text-sm text-muted-foreground">
                <Link
                    href={quickLinks.spending}
                    className="text-primary underline-offset-4 hover:underline"
                >
                    View all spending
                </Link>
                {features.transfers && (
                    <Link
                        href={quickLinks.transfers}
                        className="text-primary underline-offset-4 hover:underline"
                    >
                        View all transfers
                    </Link>
                )}
            </div>
        </div>
    );
}
