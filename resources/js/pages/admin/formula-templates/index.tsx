import { Head, Link } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';

type Props = {
    templates: Array<{
        id: string;
        name: string;
        slug: string;
        description: string | null;
    }>;
};

export default function AdminFormulaTemplatesIndex({ templates }: Props) {
    return (
        <>
            <Head title="Admin — Formula Templates" />
            <Heading
                variant="small"
                title="Savings formula templates"
                description="Edit guidance, videos, and fund bucket descriptions shown when members choose a plan."
            />
            <ul className="mt-6 space-y-3">
                {templates.map((template) => (
                    <li
                        key={template.id}
                        className="flex flex-wrap items-center justify-between gap-4 rounded-lg border p-4"
                    >
                        <div className="min-w-0">
                            <p className="font-medium">{template.name}</p>
                            <p className="text-sm text-muted-foreground">{template.slug}</p>
                            {template.description && (
                                <p className="mt-1 text-sm text-muted-foreground">
                                    {template.description}
                                </p>
                            )}
                        </div>
                        <Button variant="outline" asChild>
                            <Link href={`/admin/formula-templates/${template.id}/edit`}>
                                Edit guidance
                            </Link>
                        </Button>
                    </li>
                ))}
            </ul>
        </>
    );
}
