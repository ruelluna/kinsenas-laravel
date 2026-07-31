import { Form, Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Props = { planPriceId?: string | null };

export default function BillingPay({ planPriceId }: Props) {
    return (
        <>
            <Head title="Submit payment" />
            <div className="mx-auto max-w-md">
                <Heading variant="small" title="Submit payment" description="Upload proof after PayMaya transfer." />
                <Form action="/billing/pay" method="post" encType="multipart/form-data" className="mt-6 space-y-4">
                    <input type="hidden" name="plan_price_id" value={planPriceId ?? ''} />
                    <div className="grid gap-2">
                        <Label htmlFor="reference_number">Reference number</Label>
                        <Input id="reference_number" name="reference_number" required />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="proof_image">Proof screenshot</Label>
                        <Input id="proof_image" name="proof_image" type="file" accept="image/*" />
                    </div>
                    <Button type="submit">Submit for review</Button>
                </Form>
            </div>
        </>
    );
}
