import { Form, Head, Link } from '@inertiajs/react';
import ContentEntityTabs from '@/components/admin/content-entity-tabs';
import SideHustleFormFields from '@/components/admin/side-hustle-form-fields';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';

type Props = {
    categoryOptions: Array<{ id: string; name: string }>;
};

export default function AdminSideHustleCreate({ categoryOptions }: Props) {
    return (
        <>
            <Head title="Admin — New side hustle" />
            <ContentEntityTabs entity="side-hustles" section="list" />
            <Heading variant="small" title="New side hustle" />
            <Form
                action="/admin/content/side-hustles"
                method="post"
                className="mt-6 max-w-3xl space-y-4"
            >
                <SideHustleFormFields categoryOptions={categoryOptions} />
                <div className="flex gap-2">
                    <Button type="submit">Create side hustle</Button>
                    <Button variant="outline" asChild>
                        <Link href="/admin/content/side-hustles">Cancel</Link>
                    </Button>
                </div>
            </Form>
        </>
    );
}
