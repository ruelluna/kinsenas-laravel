import { Form, Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Props = {
    config: {
        label: string;
        instructions: string | null;
        qrImageUrl: string | null;
        isActive: boolean;
    } | null;
};

export default function AdminPaymentQrEdit({ config }: Props) {
    return (
        <>
            <Head title="Admin — Payment QR" />
            <Heading variant="small" title="PayMaya QR" description="Replace the QR shown to subscribers." />
            <Form action="/admin/payment-qr" method="post" encType="multipart/form-data" className="mt-6 max-w-md space-y-4">
                <div className="grid gap-2">
                    <Label htmlFor="label">Label</Label>
                    <Input id="label" name="label" defaultValue={config?.label ?? 'PayMaya'} required />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="instructions">Instructions</Label>
                    <textarea id="instructions" name="instructions" defaultValue={config?.instructions ?? ''} className="border-input min-h-24 rounded-md border px-3 py-2 text-sm" />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="qr_image">QR image</Label>
                    <Input id="qr_image" name="qr_image" type="file" accept="image/*" />
                </div>
                {config?.qrImageUrl && <img src={config.qrImageUrl} alt="Current QR" className="max-w-xs rounded border" />}
                <label className="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" defaultChecked={config?.isActive ?? true} />
                    Active
                </label>
                <Button type="submit">Save</Button>
            </Form>
        </>
    );
}
