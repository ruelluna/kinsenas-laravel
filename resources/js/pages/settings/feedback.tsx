import { Form, Head, usePage } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/settings/feedback';
import type { FilterOption } from '@/types/billing';

type Props = {
    categories: FilterOption[];
};

export default function FeedbackSettings({ categories }: Props) {
    const page = usePage<{ flash?: { toast?: { type: string; message: string } } }>();

    return (
        <>
            <Head title="Beta feedback" />
            <Heading
                variant="small"
                title="Beta feedback"
                description="Tell us what is working, what is confusing, or what you would like to see next."
            />

            {page.props.flash?.toast?.type === 'success' && (
                <p className="mt-4 text-sm font-medium text-green-600">
                    {page.props.flash.toast.message}
                </p>
            )}

            <Form {...store.form()} className="mt-6 space-y-6">
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-2">
                            <Label htmlFor="category">Category</Label>
                            <select
                                id="category"
                                name="category"
                                className="border-input h-9 rounded-md border px-3 text-sm"
                                defaultValue=""
                            >
                                <option value="">General feedback</option>
                                {categories.map((category) => (
                                    <option key={category.value} value={category.value}>
                                        {category.label}
                                    </option>
                                ))}
                            </select>
                            <InputError message={errors.category} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="message">Your feedback</Label>
                            <textarea
                                id="message"
                                name="message"
                                required
                                rows={6}
                                placeholder="Share bugs, ideas, or anything that would make Kinsenas better for you."
                                className="border-input min-h-24 w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                            />
                            <InputError message={errors.message} />
                        </div>

                        <Button type="submit" disabled={processing}>
                            {processing && <Spinner />}
                            Send feedback
                        </Button>
                    </>
                )}
            </Form>
        </>
    );
}

FeedbackSettings.layout = {
    breadcrumbs: [
        {
            title: 'Beta feedback',
            href: '/settings/feedback',
        },
    ],
};
