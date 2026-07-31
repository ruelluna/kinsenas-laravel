import { Form, Head, usePage } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';
import { useMemo, useState } from 'react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type {
    CategoryAllocationType,
    DeductionMode,
    FormulaTemplate,
    SavingsCategory,
    SavingsPlan,
} from '@/types/savings';
import type { SharedData } from '@/types';

type Props = {
    plan: SavingsPlan | null;
    templates: FormulaTemplate[];
};

type CategoryRow = {
    key: string;
    id?: string;
    name: string;
    allocationType: CategoryAllocationType;
    percentage: string;
    deductionMode: DeductionMode | '';
    deductionValue: string;
    deductFromIndex: string;
};

let nextRowKey = 0;

function createRowKey(prefix = 'row'): string {
    nextRowKey += 1;

    return `${prefix}-${nextRowKey}`;
}

function createEmptyRow(): CategoryRow {
    return {
        key: createRowKey(),
        name: '',
        allocationType: 'percentage',
        percentage: '',
        deductionMode: 'fixed',
        deductionValue: '',
        deductFromIndex: '',
    };
}

function createEmptyCustomRow(): CategoryRow {
    return {
        key: createRowKey(),
        name: '',
        allocationType: 'deduction',
        percentage: '',
        deductionMode: '',
        deductionValue: '',
        deductFromIndex: '',
    };
}

function rowsFromPlan(categories: SavingsCategory[]): CategoryRow[] {
    if (categories.length === 0) {
        return [createEmptyRow()];
    }

    return categories.map((category, index) => {
        const sourceIndex =
            category.deductFromCategoryId !== undefined &&
            category.deductFromCategoryId !== null
                ? categories.findIndex((c) => c.id === category.deductFromCategoryId)
                : -1;

        return {
            key: category.id ?? `row-${index}`,
            id: category.id,
            name: category.name,
            allocationType: category.allocationType ?? 'percentage',
            percentage: category.percentage ?? '',
            deductionMode: category.deductionMode ?? '',
            deductionValue: category.deductionValue ?? '',
            deductFromIndex: sourceIndex >= 0 ? String(sourceIndex) : '',
        };
    });
}

function selectClassName(disabled: boolean): string {
    return [
        'border-input flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs',
        'outline-none disabled:cursor-not-allowed disabled:opacity-50',
    ].join(' ');
}

function isExistingRow(row: CategoryRow, percentagesLocked: boolean): boolean {
    return percentagesLocked && row.id !== undefined;
}

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

    return <SavingsPlanEditor plan={plan} teamSlug={teamSlug} />;
}

function SavingsPlanEditor({
    plan,
    teamSlug,
}: {
    plan: SavingsPlan;
    teamSlug: string;
}) {
    const [rows, setRows] = useState<CategoryRow[]>(() => rowsFromPlan(plan.categories));

    const percentageTotal = useMemo(
        () =>
            rows.reduce((total, row) => {
                if (row.allocationType !== 'percentage') {
                    return total;
                }

                const value = parseFloat(row.percentage);

                return total + (Number.isFinite(value) ? value : 0);
            }, 0),
        [rows],
    );

    const percentageRows = useMemo(
        () =>
            rows
                .map((row, index) => ({ row, index }))
                .filter(({ row }) => row.allocationType === 'percentage'),
        [rows],
    );

    const percentageTotalValid = Math.abs(percentageTotal - 100) < 0.01;

    const updateRow = (index: number, patch: Partial<CategoryRow>) => {
        setRows((current) =>
            current.map((row, rowIndex) =>
                rowIndex === index ? { ...row, ...patch } : row,
            ),
        );
    };

    const addRow = () => {
        setRows((current) => [
            ...current,
            plan.percentagesLocked ? createEmptyCustomRow() : createEmptyRow(),
        ]);
    };

    const removeRow = (index: number) => {
        setRows((current) => {
            if (current.length <= 1) {
                return current;
            }

            const row = current[index];

            if (isExistingRow(row, plan.percentagesLocked)) {
                return current;
            }

            const next = current.filter((_, rowIndex) => rowIndex !== index);

            return next.map((item) => {
                if (item.allocationType !== 'deduction' || item.deductFromIndex === '') {
                    return item;
                }

                const sourceIndex = Number(item.deductFromIndex);

                if (sourceIndex === index) {
                    return { ...item, deductFromIndex: '' };
                }

                if (sourceIndex > index) {
                    return { ...item, deductFromIndex: String(sourceIndex - 1) };
                }

                return item;
            });
        });
    };

    const planDescription = plan.percentagesLocked
        ? 'Percentages are locked after your first income entry. You can still add custom categories.'
        : 'Percentage categories must total 100%. Custom categories can use optional defaults or amounts set per income.';

    return (
        <>
            <Head title="Savings Plan" />
            <Heading variant="small" title={plan.name} description={planDescription} />

            <Form
                action={`/${teamSlug}/savings/plan`}
                method="put"
                className="mt-6 space-y-6"
            >
                <div className="space-y-4">
                    {rows.map((row, index) => {
                        const rowLocked = isExistingRow(row, plan.percentagesLocked);
                        const canRemove =
                            rows.length > 1 &&
                            (!plan.percentagesLocked ||
                                (row.allocationType === 'deduction' && row.id === undefined));

                        return (
                            <div key={row.key} className="rounded-lg border p-4">
                                <div className="mb-4 flex items-center justify-between gap-2">
                                    <p className="text-sm font-medium">
                                        Category {index + 1}
                                        {rowLocked && (
                                            <span className="ml-2 text-xs font-normal text-muted-foreground">
                                                Locked
                                            </span>
                                        )}
                                    </p>
                                    {canRemove && (
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            onClick={() => removeRow(index)}
                                        >
                                            <Trash2 className="size-4" />
                                            Remove
                                        </Button>
                                    )}
                                </div>

                                {row.id && (
                                    <input
                                        type="hidden"
                                        name={`categories[${index}][id]`}
                                        value={row.id}
                                    />
                                )}

                                <div className="grid gap-4 sm:grid-cols-2">
                                    <div className="sm:col-span-2">
                                        <Label htmlFor={`category-name-${index}`}>Name</Label>
                                        <Input
                                            id={`category-name-${index}`}
                                            name={`categories[${index}][name]`}
                                            value={row.name}
                                            onChange={(event) =>
                                                updateRow(index, { name: event.target.value })
                                            }
                                            disabled={rowLocked}
                                            required
                                        />
                                    </div>

                                    {!plan.percentagesLocked || row.id === undefined ? (
                                        <div>
                                            <Label htmlFor={`category-type-${index}`}>Type</Label>
                                            <select
                                                id={`category-type-${index}`}
                                                name={`categories[${index}][allocation_type]`}
                                                className={selectClassName(rowLocked)}
                                                value={row.allocationType}
                                                onChange={(event) => {
                                                    const allocationType = event.target
                                                        .value as CategoryAllocationType;

                                                    updateRow(index, {
                                                        allocationType,
                                                        ...(allocationType === 'deduction'
                                                            ? {
                                                                  deductionMode: '',
                                                                  deductionValue: '',
                                                              }
                                                            : {}),
                                                    });
                                                }}
                                                disabled={rowLocked}
                                            >
                                                <option value="percentage">Percentage</option>
                                                <option value="deduction">Custom</option>
                                            </select>
                                        </div>
                                    ) : (
                                        <>
                                            <input
                                                type="hidden"
                                                name={`categories[${index}][allocation_type]`}
                                                value={row.allocationType}
                                            />
                                            <div>
                                                <Label>Type</Label>
                                                <p className="text-sm text-muted-foreground">
                                                    {row.allocationType === 'percentage'
                                                        ? 'Percentage'
                                                        : 'Custom'}
                                                </p>
                                            </div>
                                        </>
                                    )}

                                    {row.allocationType === 'percentage' ? (
                                        <div>
                                            <Label htmlFor={`category-percentage-${index}`}>
                                                Percentage
                                            </Label>
                                            <Input
                                                id={`category-percentage-${index}`}
                                                name={`categories[${index}][percentage]`}
                                                type="number"
                                                step="0.01"
                                                min="0.01"
                                                max="100"
                                                value={row.percentage}
                                                onChange={(event) =>
                                                    updateRow(index, {
                                                        percentage: event.target.value,
                                                    })
                                                }
                                                disabled={rowLocked}
                                                required
                                            />
                                        </div>
                                    ) : (
                                        <>
                                            <div>
                                                <Label htmlFor={`category-mode-${index}`}>
                                                    Default mode (optional)
                                                </Label>
                                                <select
                                                    id={`category-mode-${index}`}
                                                    name={`categories[${index}][deduction_mode]`}
                                                    className={selectClassName(rowLocked)}
                                                    value={row.deductionMode}
                                                    onChange={(event) =>
                                                        updateRow(index, {
                                                            deductionMode: event.target
                                                                .value as DeductionMode | '',
                                                            deductionValue:
                                                                event.target.value === ''
                                                                    ? ''
                                                                    : row.deductionValue,
                                                        })
                                                    }
                                                    disabled={rowLocked}
                                                >
                                                    <option value="">
                                                        Set per income period
                                                    </option>
                                                    <option value="fixed">Fixed amount (₱)</option>
                                                    <option value="percent_of_income">
                                                        % of income
                                                    </option>
                                                </select>
                                            </div>

                                            <div>
                                                <Label htmlFor={`category-value-${index}`}>
                                                    {row.deductionMode === 'percent_of_income'
                                                        ? 'Default % of income (optional)'
                                                        : 'Default amount (₱, optional)'}
                                                </Label>
                                                <Input
                                                    id={`category-value-${index}`}
                                                    name={`categories[${index}][deduction_value]`}
                                                    type="number"
                                                    step="0.01"
                                                    min="0.01"
                                                    value={row.deductionValue}
                                                    onChange={(event) =>
                                                        updateRow(index, {
                                                            deductionValue: event.target.value,
                                                        })
                                                    }
                                                    disabled={
                                                        rowLocked || row.deductionMode === ''
                                                    }
                                                    placeholder="Enter on income page"
                                                />
                                            </div>

                                            <div className="sm:col-span-2">
                                                <Label htmlFor={`category-source-${index}`}>
                                                    Deduct from
                                                </Label>
                                                <select
                                                    id={`category-source-${index}`}
                                                    name={`categories[${index}][deduct_from_index]`}
                                                    className={selectClassName(rowLocked)}
                                                    value={row.deductFromIndex}
                                                    onChange={(event) =>
                                                        updateRow(index, {
                                                            deductFromIndex: event.target.value,
                                                        })
                                                    }
                                                    disabled={rowLocked}
                                                    required
                                                >
                                                    <option value="">Select a category</option>
                                                    {percentageRows.map(
                                                        ({ row: sourceRow, index: sourceIndex }) => (
                                                            <option
                                                                key={`${sourceRow.key}-${sourceIndex}`}
                                                                value={String(sourceIndex)}
                                                                disabled={sourceIndex === index}
                                                            >
                                                                {sourceRow.name ||
                                                                    `Category ${sourceIndex + 1}`}
                                                            </option>
                                                        ),
                                                    )}
                                                </select>
                                            </div>
                                        </>
                                    )}
                                </div>
                            </div>
                        );
                    })}
                </div>

                <Button type="button" variant="outline" onClick={addRow}>
                    <Plus className="size-4" />
                    {plan.percentagesLocked ? 'Add custom category' : 'Add category'}
                </Button>

                {!plan.percentagesLocked && (
                    <div
                        className={`rounded-lg border px-4 py-3 text-sm ${
                            percentageTotalValid
                                ? 'border-border bg-muted/30'
                                : 'border-destructive/50 bg-destructive/5 text-destructive'
                        }`}
                    >
                        Percentage total: {percentageTotal.toFixed(2)}%
                        {!percentageTotalValid && ' — must equal 100%'}
                    </div>
                )}

                {plan.percentagesLocked && (
                    <div className="rounded-lg border border-border bg-muted/30 px-4 py-3 text-sm">
                        Percentage total: {percentageTotal.toFixed(2)}% (locked)
                    </div>
                )}

                <label className="flex items-center gap-2 text-sm">
                    <input
                        type="checkbox"
                        name="is_shared_with_team"
                        value="1"
                        defaultChecked={plan.isSharedWithTeam}
                    />
                    Share plan with team members
                </label>

                <Button type="submit">Save plan</Button>
            </Form>
        </>
    );
}

SavingsPlanPage.layout = (props: SharedData) => ({
    breadcrumbs: [
        { title: 'Savings Plan', href: `/${props.currentTeam?.slug}/savings/plan` },
    ],
});
