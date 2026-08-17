import { Form, Head } from '@inertiajs/react';
import ContentAdminTabs from '@/components/admin/content-admin-tabs';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import type { PaginatedLibrary } from '@/types/learn-library';

type Report = {
    id: string;
    reasonLabel: string;
    details: string | null;
    postTitle: string | null;
    postSlug: string | null;
    reporterName: string | null;
};

type Props = {
    reports: PaginatedLibrary<Report>;
};

export default function AdminCommunityReportsIndex({ reports }: Props) {
    return (
        <>
            <Head title="Admin — Community reports" />
            <ContentAdminTabs active="community-reports" />
            <Heading
                variant="small"
                title="Community reports"
                description="Open reports from members."
            />
            <div className="mt-6 space-y-4">
                {reports.data.map((report) => (
                    <div key={report.id} className="rounded-lg border p-4">
                        <p className="font-medium">{report.postTitle}</p>
                        <p className="text-sm text-muted-foreground">
                            {report.reasonLabel} · reported by {report.reporterName}
                        </p>
                        {report.details && (
                            <p className="mt-2 text-sm">{report.details}</p>
                        )}
                        <div className="mt-3 flex gap-2">
                            <Form
                                action={`/admin/content/community-reports/${report.id}/dismiss`}
                                method="post"
                            >
                                <Button type="submit" size="sm" variant="outline">
                                    Dismiss
                                </Button>
                            </Form>
                            <Form
                                action={`/admin/content/community-reports/${report.id}/resolve`}
                                method="post"
                            >
                                <Button type="submit" size="sm">
                                    Resolve
                                </Button>
                            </Form>
                        </div>
                    </div>
                ))}
            </div>
        </>
    );
}
