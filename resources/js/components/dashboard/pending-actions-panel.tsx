import { Form, Link } from '@inertiajs/react';
import { ArrowRightLeft, ShoppingBag } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { formatMoney } from '@/lib/format-money';
import type {
    DashboardFeatures,
    DashboardPendingActions,
    DashboardQuickLinks,
} from '@/types/dashboard';

type Props = {
    pendingActions: DashboardPendingActions;
    quickLinks: DashboardQuickLinks;
    features: DashboardFeatures;
};

export default function PendingActionsPanel({
    pendingActions,
    quickLinks,
    features,
}: Props) {
    const items = [
        ...(features.transfers ? pendingActions.transfers : []),
        ...pendingActions.spends,
    ];

    return (
        <div className="rounded-lg border p-4">
            <div className="flex items-center justify-between gap-3">
                <h3 className="font-medium">Pending actions</h3>
                <Link
                    href={quickLinks.spending}
                    className="text-sm text-primary underline-offset-4 hover:underline"
                >
                    View spending
                </Link>
            </div>
            <div className="mt-3 space-y-3">
                {items.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        Nothing waiting on you.
                    </p>
                ) : (
                    items.map((item) => (
                        <div
                            key={`${item.type}-${item.id}`}
                            className="flex items-center justify-between gap-3 rounded-md border p-3 text-sm"
                        >
                            <div className="flex items-start gap-3">
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
                            <Form action={item.confirmHref} method="post">
                                <Button
                                    type="submit"
                                    size="sm"
                                    variant="outline"
                                >
                                    Confirm
                                </Button>
                            </Form>
                        </div>
                    ))
                )}
            </div>
            {features.transfers && pendingActions.transfers.length > 0 && (
                <p className="mt-3 text-sm text-muted-foreground">
                    <Link
                        href={quickLinks.transfers}
                        className="text-primary underline-offset-4 hover:underline"
                    >
                        View all transfers
                    </Link>
                </p>
            )}
        </div>
    );
}
