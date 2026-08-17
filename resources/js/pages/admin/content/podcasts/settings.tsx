import { Head } from '@inertiajs/react';
import ContentEntityTabs from '@/components/admin/content-entity-tabs';
import Heading from '@/components/heading';

export default function AdminPodcastsSettings() {
    return (
        <>
            <Head title="Admin — Podcasts settings" />
            <ContentEntityTabs entity="podcasts" section="settings" />
            <div className="mt-4">
                <Heading
                    variant="small"
                    title="Podcasts settings"
                    description="Manage podcast shows from the list. Episodes are created from each show's edit page."
                />
            </div>
        </>
    );
}
