import { Form, Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Props = {
    guidance: {
        chooserIntro: string | null;
        chooserVideoUrl: string | null;
        beforeChooseNote: string | null;
        afterIncomeRules: string | null;
        afterIncomeVideoUrl: string | null;
    };
};

const textareaClassName =
    'border-input min-h-24 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none';

export default function AdminSavingsPlanGuidanceEdit({ guidance }: Props) {
    return (
        <>
            <Head title="Admin — Savings Plan Guidance" />
            <Heading
                variant="small"
                title="Savings plan guidance"
                description="Content shown on the member Savings Plan page — chooser intro, warnings, and post-income rules."
            />
            <Form
                action="/admin/savings-plan-guidance"
                method="put"
                className="mt-6 max-w-2xl space-y-6"
            >
                <div className="grid gap-2">
                    <Label htmlFor="chooser_intro">Chooser intro</Label>
                    <textarea
                        id="chooser_intro"
                        name="chooser_intro"
                        defaultValue={guidance.chooserIntro ?? ''}
                        className={textareaClassName}
                        placeholder="Explain why choosing a formula matters…"
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="chooser_video_url">Chooser video URL</Label>
                    <Input
                        id="chooser_video_url"
                        name="chooser_video_url"
                        type="url"
                        defaultValue={guidance.chooserVideoUrl ?? ''}
                        placeholder="https://www.youtube.com/watch?v=…"
                    />
                    <p className="text-xs text-muted-foreground">
                        YouTube or Vimeo watch or embed URL. Leave empty to hide
                        the video.
                    </p>
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="before_choose_note">
                        Before you choose note
                    </Label>
                    <textarea
                        id="before_choose_note"
                        name="before_choose_note"
                        defaultValue={guidance.beforeChooseNote ?? ''}
                        className={textareaClassName}
                        placeholder="One plan per team; percentages lock after first income…"
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="after_income_rules">
                        After income rules
                    </Label>
                    <textarea
                        id="after_income_rules"
                        name="after_income_rules"
                        defaultValue={guidance.afterIncomeRules ?? ''}
                        className={textareaClassName}
                        placeholder="What members can still edit once income exists…"
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="after_income_video_url">
                        After income video URL
                    </Label>
                    <Input
                        id="after_income_video_url"
                        name="after_income_video_url"
                        type="url"
                        defaultValue={guidance.afterIncomeVideoUrl ?? ''}
                        placeholder="https://player.vimeo.com/video/…"
                    />
                </div>
                <Button type="submit">Save guidance</Button>
            </Form>
        </>
    );
}
