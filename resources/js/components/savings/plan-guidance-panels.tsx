import { Link } from '@inertiajs/react';
import { Info, Landmark } from 'lucide-react';
import VideoEmbed from '@/components/savings/video-embed';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
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
        afterFirstIncome:
            'Toggle on plan — enables Edit and Delete on Spending',
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
                        <p className="text-sm whitespace-pre-wrap text-muted-foreground">
                            {pageGuidance.afterIncomeRules}
                        </p>
                    )}
                </div>
            </div>

            <VideoEmbed
                url={pageGuidance.afterIncomeVideoUrl}
                title="After income rules"
            />

            <div className="overflow-x-auto rounded-md border bg-background md:hidden">
                <div className="divide-y">
                    {editRulesRows.map((row) => (
                        <div key={row.categoryType} className="space-y-1 p-3">
                            <p className="text-sm font-medium">
                                {row.categoryType}
                            </p>
                            <p className="text-sm text-muted-foreground">
                                {row.afterFirstIncome}
                            </p>
                        </div>
                    ))}
                </div>
            </div>

            <div className="hidden overflow-x-auto rounded-md border bg-background md:block">
                <table className="w-full text-left text-sm">
                    <thead>
                        <tr className="border-b bg-muted/40">
                            <th className="px-3 py-2 font-medium">
                                Fund bucket type
                            </th>
                            <th className="px-3 py-2 font-medium">
                                After first income
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {editRulesRows.map((row) => (
                            <tr
                                key={row.categoryType}
                                className="border-b last:border-0"
                            >
                                <td className="px-3 py-2 align-top font-medium">
                                    {row.categoryType}
                                </td>
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

export function BeforeChooseAlert({ note }: { note: string | null }) {
    if (!note) {
        return null;
    }

    return (
        <Alert variant="guidance" className="mt-6">
            <Info />
            <AlertTitle>Before you choose</AlertTitle>
            <AlertDescription className="whitespace-pre-wrap">
                {note}
            </AlertDescription>
        </Alert>
    );
}

export function BanksFirstAlert({
    teamSlug,
    hasBanks,
}: {
    teamSlug: string;
    hasBanks: boolean;
}) {
    if (hasBanks || teamSlug === '') {
        return null;
    }

    return (
        <Alert variant="warning" className="mt-6" data-tour="banks-first">
            <Landmark />
            <AlertTitle>Add your banks first</AlertTitle>
            <AlertDescription className="space-y-3">
                <p>
                    Add the bank accounts you use before picking a formula.
                    After you choose a plan, you&apos;ll assign each fund bucket
                    to one of those accounts. You can still pick a plan now, but
                    bank assignment will be empty until you add banks.
                </p>
                <Button variant="outline" size="sm" asChild>
                    <Link href={`/${teamSlug}/savings/banks`}>Go to Banks</Link>
                </Button>
            </AlertDescription>
        </Alert>
    );
}
