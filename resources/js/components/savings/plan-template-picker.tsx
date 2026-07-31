import { Form } from '@inertiajs/react';
import VideoEmbed from '@/components/savings/video-embed';
import { BeforeChooseAlert } from '@/components/savings/plan-guidance-panels';
import { Button } from '@/components/ui/button';
import type { FormulaTemplate, SavingsPlanPageGuidance } from '@/types/savings';

function TemplatePercentageBar({ categories }: { categories: FormulaTemplate['categories'] }) {
    const colors = [
        'bg-primary/80',
        'bg-primary/60',
        'bg-primary/45',
        'bg-primary/35',
        'bg-primary/25',
        'bg-muted-foreground/40',
        'bg-muted-foreground/25',
    ];

    return (
        <div className="flex h-2 overflow-hidden rounded-full bg-muted">
            {categories.map((category, index) => {
                const width = parseFloat(category.percentage);

                if (!Number.isFinite(width) || width <= 0) {
                    return null;
                }

                return (
                    <div
                        key={`${category.name}-${index}`}
                        className={colors[index % colors.length]}
                        style={{ width: `${width}%` }}
                        title={`${category.name} ${category.percentage}%`}
                    />
                );
            })}
        </div>
    );
}

function TemplatePickerCard({
    template,
    teamSlug,
}: {
    template: FormulaTemplate;
    teamSlug: string;
}) {
    return (
        <Form
            action={`/${teamSlug}/savings/plan/from-template/${template.id}`}
            method="post"
            className="flex flex-col rounded-lg border p-4"
        >
            <h3 className="font-medium">{template.name}</h3>
            {template.description && (
                <p className="mt-1 text-sm text-muted-foreground">{template.description}</p>
            )}
            {template.bestFor && (
                <p className="mt-2 text-sm">
                    <span className="font-medium">Best for: </span>
                    <span className="text-muted-foreground">{template.bestFor}</span>
                </p>
            )}

            <VideoEmbed
                url={template.videoEmbedUrl}
                title={`${template.name} explainer`}
                className="mt-4"
            />

            <div className="mt-4 space-y-3">
                <TemplatePercentageBar categories={template.categories} />
                <ul className="space-y-2 text-sm">
                    {template.categories.map((category) => (
                        <li key={category.name} className="flex gap-2">
                            <span className="shrink-0 font-medium tabular-nums">
                                {category.percentage}%
                            </span>
                            <div className="min-w-0">
                                <p className="font-medium">{category.name}</p>
                                {category.description && (
                                    <p className="text-muted-foreground">{category.description}</p>
                                )}
                            </div>
                        </li>
                    ))}
                </ul>
            </div>

            <Button type="submit" className="mt-auto pt-4">
                Use this formula
            </Button>
        </Form>
    );
}

export default function SavingsPlanTemplatePicker({
    templates,
    pageGuidance,
    teamSlug,
}: {
    templates: FormulaTemplate[];
    pageGuidance: SavingsPlanPageGuidance;
    teamSlug: string;
}) {
    return (
        <div className="mt-6 space-y-6">
            {(pageGuidance.chooserIntro || pageGuidance.chooserVideoUrl) && (
                <div className="space-y-4 rounded-lg border bg-muted/20 p-4">
                    {pageGuidance.chooserIntro && (
                        <p className="text-sm whitespace-pre-wrap text-muted-foreground">
                            {pageGuidance.chooserIntro}
                        </p>
                    )}
                    <VideoEmbed url={pageGuidance.chooserVideoUrl} title="Choosing a savings formula" />
                </div>
            )}

            <BeforeChooseAlert note={pageGuidance.beforeChooseNote} />

            <div className="grid gap-4 lg:grid-cols-2">
                {templates.map((template) => (
                    <TemplatePickerCard
                        key={template.id}
                        template={template}
                        teamSlug={teamSlug}
                    />
                ))}
            </div>
        </div>
    );
}
