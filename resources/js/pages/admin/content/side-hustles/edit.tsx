import { Form, Head, Link } from '@inertiajs/react';
import ContentEntityTabs from '@/components/admin/content-entity-tabs';
import SideHustleFormFields from '@/components/admin/side-hustle-form-fields';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import type { SideHustleAdmin } from '@/types/learn-library';

type Props = {
    hustle: SideHustleAdmin;
    categoryOptions: Array<{ id: string; name: string }>;
};

export default function AdminSideHustleEdit({ hustle, categoryOptions }: Props) {
    return (
        <>
            <Head title={`Admin — ${hustle.title}`} />
            <ContentEntityTabs entity="side-hustles" section="list" />
            <Heading variant="small" title={hustle.title} />
            <Form
                action={`/admin/content/side-hustles/${hustle.slug}`}
                method="put"
                className="mt-6 max-w-3xl space-y-4"
            >
                <SideHustleFormFields hustle={hustle} categoryOptions={categoryOptions} />
                <div className="flex gap-2">
                    <Button type="submit">Save side hustle</Button>
                    <Button variant="outline" asChild>
                        <Link href="/admin/content/side-hustles">Back</Link>
                    </Button>
                </div>
            </Form>
        </>
    );
}
