import { Form, Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Category = {
    id: string;
    name: string;
    percentage: string;
    description: string | null;
};

type Props = {
    template: {
        id: string;
        name: string;
        slug: string;
        description: string | null;
        bestFor: string | null;
        videoEmbedUrl: string | null;
        categories: Category[];
    };
};

const textareaClassName =
    'border-input min-h-20 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none';

export default function AdminFormulaTemplateEdit({ template }: Props) {
    return (
        <>
            <Head title={`Admin — ${template.name}`} />
            <div className="mb-4">
                <Button variant="ghost" size="sm" asChild>
                    <Link href="/admin/formula-templates">
                        <ArrowLeft className="size-4" />
                        Back to templates
                    </Link>
                </Button>
            </div>
            <Heading
                variant="small"
                title={template.name}
                description={`Slug: ${template.slug}. Category names and percentages are fixed; edit descriptions and guidance below.`}
            />
            <Form
                action={`/admin/formula-templates/${template.id}`}
                method="put"
                className="mt-6 max-w-2xl space-y-6"
            >
                <div className="grid gap-2">
                    <Label htmlFor="description">Short tagline</Label>
                    <Input
                        id="description"
                        name="description"
                        defaultValue={template.description ?? ''}
                        placeholder="One-line summary for the template card"
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="best_for">Best for</Label>
                    <textarea
                        id="best_for"
                        name="best_for"
                        defaultValue={template.bestFor ?? ''}
                        className={textareaClassName}
                        placeholder="Who should pick this formula…"
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="video_embed_url">Explainer video URL</Label>
                    <Input
                        id="video_embed_url"
                        name="video_embed_url"
                        type="url"
                        defaultValue={template.videoEmbedUrl ?? ''}
                        placeholder="https://www.youtube.com/watch?v=…"
                    />
                </div>

                <div className="space-y-4">
                    <p className="text-sm font-medium">Category descriptions</p>
                    {template.categories.map((category, index) => (
                        <div key={category.id} className="rounded-lg border p-4">
                            <input
                                type="hidden"
                                name={`categories[${index}][id]`}
                                value={category.id}
                            />
                            <p className="font-medium">
                                {category.name}{' '}
                                <span className="text-muted-foreground">({category.percentage}%)</span>
                            </p>
                            <div className="mt-3 grid gap-2">
                                <Label htmlFor={`category-description-${category.id}`}>
                                    Purpose (shown to members)
                                </Label>
                                <textarea
                                    id={`category-description-${category.id}`}
                                    name={`categories[${index}][description]`}
                                    defaultValue={category.description ?? ''}
                                    className={textareaClassName}
                                    placeholder="Describe what this fund is for…"
                                />
                            </div>
                        </div>
                    ))}
                </div>

                <Button type="submit">Save template</Button>
            </Form>
        </>
    );
}
