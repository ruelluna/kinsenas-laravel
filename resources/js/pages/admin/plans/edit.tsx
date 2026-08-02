import { Form, Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { PlanFormFields } from '@/components/admin/plan-form-fields';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import type { AdminPlan, SubscriptionFeatureOption } from '@/types/billing';

type Props = {
    plan: AdminPlan;
    features: SubscriptionFeatureOption[];
};

export default function AdminPlansEdit({ plan, features }: Props) {
    return (
        <>
            <Head title={`Admin — Edit ${plan.name}`} />
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
                title={`Edit ${plan.name}`}
                description={`Slug: ${plan.slug}`}
            />
            <Form
                action={`/admin/plans/${plan.id}`}
                method="put"
                className="mt-6 max-w-2xl space-y-4"
            >
                <PlanFormFields plan={plan} features={features} />
                <Button type="submit">Save plan</Button>
            </Form>
        </>
    );
}
