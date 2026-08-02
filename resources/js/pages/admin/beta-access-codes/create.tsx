import { Form, Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';

export default function AdminBetaAccessCodesCreate() {
    return (
        <>
            <Head title="Admin — Create beta access codes" />
            <div className="mb-4">
                <Button variant="ghost" size="sm" asChild>
                    <Link href="/admin/beta-access-codes">
                        <ArrowLeft className="size-4" />
                        Back to codes
                    </Link>
                </Button>
            </div>

            <Heading
                variant="small"
                title="Create beta access codes"
                description="Shared event codes for calling cards, or single-use batches for unique cards."
            />

            <section className="mt-6 max-w-2xl space-y-4">
                <h2 className="text-base font-medium">Event code</h2>
                <p className="text-sm text-muted-foreground">
                    One shared code for all attendees at an event — ideal for
                    calling cards and QR links.
                </p>
                <Form
                    action="/admin/beta-access-codes"
                    method="post"
                    className="space-y-4"
                >
                    <div className="grid gap-2">
                        <Label htmlFor="code">Code</Label>
                        <Input
                            id="code"
                            name="code"
                            required
                            placeholder="KINSENAS-MNL-2026"
                            className="font-mono uppercase"
                        />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="label">Event label</Label>
                        <Input
                            id="label"
                            name="label"
                            required
                            placeholder="Manila Finance Expo 2026"
                        />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="max_uses">Max uses (optional)</Label>
                        <Input
                            id="max_uses"
                            name="max_uses"
                            type="number"
                            min={1}
                            placeholder="Leave blank for unlimited"
                        />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="expires_at">
                            Expires at (optional)
                        </Label>
                        <Input
                            id="expires_at"
                            name="expires_at"
                            type="datetime-local"
                        />
                    </div>

                    <Button type="submit">Create event code</Button>
                </Form>
            </section>

            <Separator className="my-8 max-w-2xl" />

            <section className="max-w-2xl space-y-4">
                <h2 className="text-base font-medium">Single-use batch</h2>
                <p className="text-sm text-muted-foreground">
                    Generate unique codes for individual cards. Export CSV after
                    creation.
                </p>
                <Form
                    action="/admin/beta-access-codes/batches"
                    method="post"
                    className="space-y-4"
                >
                    <div className="grid gap-2">
                        <Label htmlFor="batch_name">Batch name</Label>
                        <Input
                            id="batch_name"
                            name="name"
                            required
                            placeholder="Manila Expo — card batch 1"
                        />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="quantity">Quantity</Label>
                        <Input
                            id="quantity"
                            name="quantity"
                            type="number"
                            min={1}
                            max={500}
                            defaultValue={50}
                            required
                        />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="batch_expires_at">
                            Expires at (optional)
                        </Label>
                        <Input
                            id="batch_expires_at"
                            name="expires_at"
                            type="datetime-local"
                        />
                    </div>

                    <Button type="submit">Generate batch</Button>
                </Form>
            </section>
        </>
    );
}
