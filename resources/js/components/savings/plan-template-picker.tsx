import { Form } from '@inertiajs/react';
import {
    BanksFirstAlert,
    BeforeChooseAlert,
} from '@/components/savings/plan-guidance-panels';
import TemplateAllocationPieChart from '@/components/savings/template-allocation-pie-chart';
import VideoEmbed from '@/components/savings/video-embed';
import { Button } from '@/components/ui/button';
import type { FormulaTemplate, SavingsPlanPageGuidance } from '@/types/savings';

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
                <p className="mt-1 text-sm text-muted-foreground">
                    {template.description}
                </p>
            )}
            {template.bestFor && (
                <p className="mt-2 text-sm">
                    <span className="font-medium">Best for: </span>
                    <span className="text-muted-foreground">
                        {template.bestFor}
                    </span>
                </p>
            )}

            <VideoEmbed
                url={template.videoEmbedUrl}
                title={`${template.name} explainer`}
                className="mt-4"
            />

            <div className="mt-4">
                <p className="mb-3 text-xs text-muted-foreground">
                    Hover a slice to read each fund&apos;s description.
                </p>
                <TemplateAllocationPieChart categories={template.categories} />
            </div>

            <div className="mt-6 flex justify-center">
                <Button type="submit" className="w-fit">
                    Use this formula
                </Button>
            </div>
        </Form>
    );
}

function CustomPlanCard({ teamSlug }: { teamSlug: string }) {
    return (
        <Form
            action={`/${teamSlug}/savings/plan/custom`}
            method="post"
            className="flex flex-col rounded-lg border p-4"
        >
            <h3 className="font-medium">Build your own</h3>
            <p className="mt-1 text-sm text-muted-foreground">
                Define your own fund names and percentage split from scratch.
            </p>
            <p className="mt-2 text-sm">
                <span className="font-medium">Best for: </span>
                <span className="text-muted-foreground">
                    Members who already know how they want to divide income, or
                    want a layout that does not match Abundant or TRC.
                </span>
            </p>

            <div className="mt-6 flex flex-1 flex-col justify-end">
                <div className="flex justify-center">
                    <Button type="submit" variant="outline" className="w-fit">
                        Start custom plan
                    </Button>
                </div>
            </div>
        </Form>
    );
}

export default function SavingsPlanTemplatePicker({
    templates,
    pageGuidance,
    teamSlug,
    hasBanks,
}: {
    templates: FormulaTemplate[];
    pageGuidance: SavingsPlanPageGuidance;
    teamSlug: string;
    hasBanks: boolean;
}) {
    return (
        <div className="mt-6 space-y-6">
            <BanksFirstAlert teamSlug={teamSlug} hasBanks={hasBanks} />

            {(pageGuidance.chooserIntro || pageGuidance.chooserVideoUrl) && (
                <div className="space-y-4 rounded-lg border bg-muted/20 p-4">
                    {pageGuidance.chooserIntro && (
                        <p className="text-sm whitespace-pre-wrap text-muted-foreground">
                            {pageGuidance.chooserIntro}
                        </p>
                    )}
                    <VideoEmbed
                        url={pageGuidance.chooserVideoUrl}
                        title="Choosing a savings formula"
                    />
                </div>
            )}

            <BeforeChooseAlert note={pageGuidance.beforeChooseNote} />

            <div
                className="grid gap-4 lg:grid-cols-2"
                data-tour="plan-templates"
            >
                {templates.map((template) => (
                    <TemplatePickerCard
                        key={template.id}
                        template={template}
                        teamSlug={teamSlug}
                    />
                ))}
                <CustomPlanCard teamSlug={teamSlug} />
            </div>
        </div>
    );
}
