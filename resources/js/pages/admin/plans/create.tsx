import { Form, Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { PlanFormFields } from '@/components/admin/plan-form-fields';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import type { SubscriptionFeatureOption } from '@/types/billing';

type Props = {
    features: SubscriptionFeatureOption[];
};

export default function AdminPlansCreate({ features }: Props) {
    return (
        <>
            <Head title="Admin — Create plan" />
            <div className="mb-4">
                <Button variant="ghost" size="sm" asChild>
                    <Link href="/admin/plans">
                        <ArrowLeft className="size-4" />
                        Back to plans
                    </Link>
                </Button>
            </div>
            <Heading
                variant="small"
                title="Create subscription plan"
                description="Platform admin only."
            />
            <Form
                action="/admin/plans"
                method="post"
                className="mt-6 max-w-2xl space-y-4"
            >
                <PlanFormFields features={features} />
                <Button type="submit">Create plan</Button>
            </Form>
        </>
    );
}
