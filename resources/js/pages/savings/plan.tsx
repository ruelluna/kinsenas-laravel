import { Form, Head, usePage } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { FormulaTemplate, SavingsPlan } from '@/types/savings';
import type { SharedData } from '@/types';

type Props = {
    plan: SavingsPlan | null;
    templates: FormulaTemplate[];
};

export default function SavingsPlanPage({ plan, templates }: Props) {
    const { currentTeam } = usePage<SharedData>().props;
    const teamSlug = currentTeam?.slug ?? '';

    if (!plan) {
        return (
            <>
                <Head title="Savings Plan" />
                <Heading
                    variant="small"
                    title="Choose a savings formula"
                    description="Start with a preset or customize categories later."
                />
                <div className="mt-6 grid gap-4">
                    {templates.map((template) => (
                        <Form
                            key={template.id}
                            action={`/${teamSlug}/savings/plan/from-template/${template.id}`}
                            method="post"
                            className="rounded-lg border p-4"
                        >
                            <h3 className="font-medium">{template.name}</h3>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {template.description}
                            </p>
                            <ul className="mt-3 space-y-1 text-sm">
                                {template.categories.map((c) => (
                                    <li key={c.name}>
                                        {c.name} — {c.percentage}%
                                    </li>
                                ))}
                            </ul>
                            <Button type="submit" className="mt-4">
                                Use this formula
                            </Button>
                        </Form>
                    ))}
                </div>
            </>
        );
    }

    return (
        <>
            <Head title="Savings Plan" />
            <Heading
                variant="small"
                title={plan.name}
                description="Categories must total 100%. Locked income blocks edits."
            />
            <Form
                action={`/${teamSlug}/savings/plan`}
                method="put"
                className="mt-6 space-y-4"
            >
                {plan.categories.map((category, index) => (
                    <div key={category.id ?? index} className="grid gap-2 sm:grid-cols-2">
                        <input type="hidden" name={`categories[${index}][name]`} value={category.name} />
                        <div>
                            <Label>Name</Label>
                            <Input
                                name={`categories[${index}][name]`}
                                defaultValue={category.name}
                                disabled={plan.hasLockedIncome}
                            />
                        </div>
                        <div>
                            <Label>Percentage</Label>
                            <Input
                                name={`categories[${index}][percentage]`}
                                type="number"
                                step="0.01"
                                defaultValue={category.percentage}
                                disabled={plan.hasLockedIncome}
                            />
                        </div>
                    </div>
                ))}
                <label className="flex items-center gap-2 text-sm">
                    <input
                        type="checkbox"
                        name="is_shared_with_team"
                        value="1"
                        defaultChecked={plan.isSharedWithTeam}
                        disabled={plan.hasLockedIncome}
                    />
                    Share plan with team members
                </label>
                {!plan.hasLockedIncome && (
                    <Button type="submit">Save plan</Button>
                )}
            </Form>
        </>
    );
}

SavingsPlanPage.layout = (props: SharedData) => ({
    breadcrumbs: [
        { title: 'Savings Plan', href: `/${props.currentTeam?.slug}/savings/plan` },
    ],
});
