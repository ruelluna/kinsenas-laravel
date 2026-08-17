import { Head, Link } from '@inertiajs/react';
import ContentEntityTabs from '@/components/admin/content-entity-tabs';
import Heading from '@/components/heading';

export default function AdminSeriesSettings() {
    return (
        <>
            <Head title="Admin — Series settings" />
            <ContentEntityTabs entity="series" section="settings" />
            <div className="mt-4">
                <Heading
                    variant="small"
                    title="Series settings"
                    description="Series group related posts on Learn. Episodes are regular posts assigned to a series — manage them from Posts."
                />
            </div>
            <p className="mt-4 text-sm text-muted-foreground">
                To add episodes, create or edit a post and choose a series on the post form.{' '}
                <Link href="/admin/content/posts" className="text-primary underline-offset-4 hover:underline">
                    Go to Posts
                </Link>
            </p>
        </>
    );
}
