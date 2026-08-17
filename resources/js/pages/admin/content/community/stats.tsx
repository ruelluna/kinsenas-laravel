import { Head, router } from '@inertiajs/react';
import ContentEntityTabs from '@/components/admin/content-entity-tabs';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';

type Props = {
    window: string;
    summary: {
        total: number;
        published: number;
        pending: number;
        rejected: number;
        withdrawn: number;
        openReports: number;
        recentSubmissions: number;
    };
};

export default function AdminCommunityStats({ window, summary }: Props) {
    return (
        <>
            <Head title="Admin — Community stats" />
            <ContentEntityTabs entity="community" section="stats" />
            <div className="mt-4">
                <Heading
                    variant="small"
                    title="Community stats"
                    description="Post counts by status and open reports."
                />
            </div>
            <div className="mt-4 flex flex-wrap gap-2">
                {[
                    { value: 'all', label: 'All time' },
                    { value: '7', label: '7 days' },
                    { value: '30', label: '30 days' },
                ].map((item) => (
                    <Button
                        key={item.value}
                        variant={window === item.value ? 'default' : 'outline'}
                        size="sm"
                        onClick={() =>
                            router.get(
                                '/admin/content/community/stats',
                                { window: item.value },
                                { preserveState: true },
                            )
                        }
                    >
                        {item.label}
                    </Button>
                ))}
            </div>
            <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div className="rounded-lg border p-4">
                    <p className="text-sm text-muted-foreground">Total posts</p>
                    <p className="text-2xl font-semibold">{summary.total}</p>
                </div>
                <div className="rounded-lg border p-4">
                    <p className="text-sm text-muted-foreground">Published</p>
                    <p className="text-2xl font-semibold">{summary.published}</p>
                </div>
                <div className="rounded-lg border p-4">
                    <p className="text-sm text-muted-foreground">Pending review</p>
                    <p className="text-2xl font-semibold">{summary.pending}</p>
                </div>
                <div className="rounded-lg border p-4">
                    <p className="text-sm text-muted-foreground">Open reports</p>
                    <p className="text-2xl font-semibold">{summary.openReports}</p>
                </div>
            </div>
            <div className="mt-6 grid gap-4 sm:grid-cols-3">
                <div className="rounded-lg border p-4">
                    <p className="text-sm text-muted-foreground">Rejected</p>
                    <p className="text-2xl font-semibold">{summary.rejected}</p>
                </div>
                <div className="rounded-lg border p-4">
                    <p className="text-sm text-muted-foreground">Withdrawn</p>
                    <p className="text-2xl font-semibold">{summary.withdrawn}</p>
                </div>
                <div className="rounded-lg border p-4">
                    <p className="text-sm text-muted-foreground">
                        Submissions{window !== 'all' ? ` (${window} days)` : ''}
                    </p>
                    <p className="text-2xl font-semibold">{summary.recentSubmissions}</p>
                </div>
            </div>
        </>
    );
}
