import { Info } from 'lucide-react';
import VideoEmbed from '@/components/savings/video-embed';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import type { SavingsPlanPageGuidance } from '@/types/savings';

const editRulesRows = [
    {
        categoryType: 'Percentage',
        afterFirstIncome: 'Locked — name, percentage, and remove are disabled',
    },
    {
        categoryType: 'Custom',
        afterFirstIncome: 'Add, edit, and remove allowed',
    },
    {
        categoryType: 'Share with team',
        afterFirstIncome: 'Still editable',
    },
    {
        categoryType: 'Allow editing spends',
        afterFirstIncome: 'Toggle on plan — enables Edit and Delete on Spending',
    },
] as const;

export function PlanEditRulesPanel({
    pageGuidance,
}: {
    pageGuidance: SavingsPlanPageGuidance;
}) {
    return (
        <div className="mt-6 space-y-4 rounded-lg border bg-muted/20 p-4">
            <div className="flex items-start gap-2">
                <Info className="mt-0.5 size-4 shrink-0 text-primary" />
                <div className="min-w-0 space-y-1">
                    <h2 className="text-sm font-medium">What you can change</h2>
                    {pageGuidance.afterIncomeRules && (
                        <p className="text-sm text-muted-foreground whitespace-pre-wrap">
                            {pageGuidance.afterIncomeRules}
                        </p>
                    )}
                </div>
            </div>

            <VideoEmbed url={pageGuidance.afterIncomeVideoUrl} title="After income rules" />

            <div className="overflow-x-auto rounded-md border bg-background">
                <table className="w-full min-w-[320px] text-left text-sm">
                    <thead>
                        <tr className="border-b bg-muted/40">
                            <th className="px-3 py-2 font-medium">Category type</th>
                            <th className="px-3 py-2 font-medium">After first income</th>
                        </tr>
                    </thead>
                    <tbody>
                        {editRulesRows.map((row) => (
                            <tr key={row.categoryType} className="border-b last:border-0">
                                <td className="px-3 py-2 align-top font-medium">{row.categoryType}</td>
                                <td className="px-3 py-2 align-top text-muted-foreground">
                                    {row.afterFirstIncome}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

export function BeforeChooseAlert({
    note,
}: {
    note: string | null;
}) {
    if (!note) {
        return null;
    }

    return (
        <Alert className="mt-6">
            <Info className="text-primary" />
            <AlertTitle>Before you choose</AlertTitle>
            <AlertDescription className="whitespace-pre-wrap">{note}</AlertDescription>
        </Alert>
    );
}
