import { Form, Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import type { AdminBetaAccessCode, FilterOption } from '@/types/billing';

type Props = {
    codes: {
        data: AdminBetaAccessCode[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters: {
        status: string;
    };
    statusOptions: FilterOption[];
};

function redemptionLabel(code: AdminBetaAccessCode): string {
    if (code.maxUses === null) {
        return `${code.redemptionsCount} used`;
    }

    return `${code.redemptionsCount}/${code.maxUses} used`;
}

export default function AdminBetaAccessCodesIndex({ codes, filters, statusOptions }: Props) {
    return (
        <>
            <Head title="Admin — Beta access codes" />
            <div className="flex flex-wrap items-start justify-between gap-4">
                <Heading
                    variant="small"
                    title="Beta access codes"
                    description="Create event codes for calling cards or generate single-use batches."
                />
                <Button asChild size="sm">
                    <Link href="/admin/beta-access-codes/create">
                        <Plus className="size-4" />
                        Create codes
                    </Link>
                </Button>
            </div>

            <Form method="get" action="/admin/beta-access-codes" className="mt-6 flex flex-wrap items-end gap-3">
                <div className="grid gap-2">
                    <Label htmlFor="status">Status</Label>
                    <select
                        id="status"
                        name="status"
                        defaultValue={filters.status}
                        className="border-input h-9 rounded-md border px-3 text-sm"
                    >
                        {statusOptions.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                </div>
                <Button type="submit" variant="secondary" size="sm">
                    Filter
                </Button>
            </Form>

            <div className="mt-6 space-y-4">
                {codes.data.length === 0 ? (
                    <p className="text-sm text-muted-foreground">No beta access codes match this filter.</p>
                ) : (
                    codes.data.map((code) => (
                        <article key={code.id} className="rounded-lg border p-4 text-sm">
                            <div className="flex flex-wrap items-start justify-between gap-4">
                                <div className="space-y-2">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <p className="font-mono text-base font-medium">{code.code}</p>
                                        <Badge variant="secondary">{code.typeLabel}</Badge>
                                        {!code.isActive && <Badge variant="outline">Inactive</Badge>}
                                        {!code.isRedeemable && code.isActive && (
                                            <Badge variant="outline">Unavailable</Badge>
                                        )}
                                    </div>
                                    <p className="font-medium">{code.label}</p>
                                    {code.batchName && (
                                        <p className="text-muted-foreground">Batch: {code.batchName}</p>
                                    )}
                                    <p className="text-muted-foreground">{redemptionLabel(code)}</p>
                                    {code.expiresAt && (
                                        <p className="text-muted-foreground">
                                            Expires {new Date(code.expiresAt).toLocaleString()}
                                        </p>
                                    )}
                                </div>

                                <div className="flex flex-wrap gap-2">
                                    {code.batchId && (
                                        <Button variant="outline" size="sm" asChild>
                                            <a
                                                href={`/admin/beta-access-codes/batches/${code.batchId}/export`}
                                            >
                                                Export CSV
                                            </a>
                                        </Button>
                                    )}
                                    <Form
                                        action={`/admin/beta-access-codes/${code.id}`}
                                        method="patch"
                                    >
                                        <input
                                            type="hidden"
                                            name="is_active"
                                            value={code.isActive ? '0' : '1'}
                                        />
                                        <Button type="submit" size="sm" variant="outline">
                                            {code.isActive ? 'Deactivate' : 'Activate'}
                                        </Button>
                                    </Form>
                                </div>
                            </div>
                        </article>
                    ))
                )}
            </div>
        </>
    );
}
