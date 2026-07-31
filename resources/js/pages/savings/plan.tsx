import { Form, Head, Link, usePage } from '@inertiajs/react';
import { AlertTriangle, Plus, Trash2 } from 'lucide-react';
import { useMemo, useRef, useState } from 'react';
import Heading from '@/components/heading';
import {
    BeforeChooseAlert,
    PlanEditRulesPanel,
} from '@/components/savings/plan-guidance-panels';
import SavingsPlanTemplatePicker from '@/components/savings/plan-template-picker';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatMoney } from '@/lib/format-money';
import type {
    CategoryAllocationType,
    DeductionMode,
    FormulaTemplate,
    FundBalance,
    SavingsCategory,
    SavingsPlan,
    SavingsPlanPageGuidance,
} from '@/types/savings';

import type { SharedData } from '@/types';

type Props = {
    plan: SavingsPlan | null;
    templates: FormulaTemplate[];
    fundBalances: FundBalance[];
    pageGuidance: SavingsPlanPageGuidance;
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

function isPercentageRowLocked(row: CategoryRow, percentagesLocked: boolean): boolean {
    return percentagesLocked && row.allocationType === 'percentage' && row.id !== undefined;
}

function resolveDeductFromCategoryId(
    row: CategoryRow,
    rows: CategoryRow[],
): string | null {
    if (row.deductFromIndex === '') {
        return null;
    }

    const sourceRow = rows[Number(row.deductFromIndex)];

    return sourceRow?.id ?? null;
}

function hasCustomCategoryChanges(
    rows: CategoryRow[],
    initialCategories: SavingsCategory[],
): boolean {
    const initialCustom = initialCategories.filter(
        (category) => category.allocationType === 'deduction',
    );
    const currentCustom = rows.filter((row) => row.allocationType === 'deduction');

    if (currentCustom.some((row) => row.id === undefined)) {
        return true;
    }

    if (initialCustom.some(
        (category) => !currentCustom.some((row) => row.id === category.id),
    )) {
        return true;
    }

    return currentCustom.some((row) => {
        const initial = initialCategories.find((category) => category.id === row.id);

        if (!initial) {
            return false;
        }

        return (
            initial.name !== row.name
            || (initial.deductionMode ?? '') !== row.deductionMode
            || (initial.deductionValue ?? '') !== row.deductionValue
            || (initial.deductFromCategoryId ?? null)
                !== resolveDeductFromCategoryId(row, rows)
        );
    });
}

export default function SavingsPlanPage({ plan, templates, fundBalances, pageGuidance }: Props) {
    const { currentTeam } = usePage<SharedData>().props;
    const teamSlug = currentTeam?.slug ?? '';

    if (!plan) {
        return (
            <>
                <Head title="Savings Plan" />
                <Heading
                    variant="small"
                    title="Choose a savings formula"
                    description="Compare each plan below and pick the split that fits how you manage money."
                />
                <SavingsPlanTemplatePicker
                    templates={templates}
                    pageGuidance={pageGuidance}
                    teamSlug={teamSlug}
                />
            </>
        );
    }

    return (
        <SavingsPlanEditor
            plan={plan}
            teamSlug={teamSlug}
            fundBalances={fundBalances}
            pageGuidance={pageGuidance}
        />
    );
}

function SavingsPlanEditor({
    plan,
    teamSlug,
    fundBalances,
    pageGuidance,
}: {
    plan: SavingsPlan;
    teamSlug: string;
    fundBalances: FundBalance[];
    pageGuidance: SavingsPlanPageGuidance;
}) {
    const [rows, setRows] = useState<CategoryRow[]>(() => rowsFromPlan(plan.categories));
    const [confirmOpen, setConfirmOpen] = useState(false);
    const submitButtonRef = useRef<HTMLButtonElement>(null);
    const skipCustomConfirmRef = useRef(false);

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

    const customChangesPending = useMemo(
        () => hasCustomCategoryChanges(rows, plan.categories),
        [rows, plan.categories],
    );

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
            const row = current[index];

            if (isPercentageRowLocked(row, plan.percentagesLocked)) {
                return current;
            }

            const next = current.filter((_, rowIndex) => rowIndex !== index);

            if (plan.percentagesLocked && next.every((item) => item.allocationType === 'percentage')) {
                return current;
            }

            if (!plan.percentagesLocked && next.length === 0) {
                return current;
            }

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

    const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
        if (
            plan.hasIncome
            && customChangesPending
            && !skipCustomConfirmRef.current
        ) {
            event.preventDefault();
            setConfirmOpen(true);
        } else {
            skipCustomConfirmRef.current = false;
        }
    };

    const confirmSave = () => {
        skipCustomConfirmRef.current = true;
        setConfirmOpen(false);
        submitButtonRef.current?.click();
    };

    const planDescription = plan.percentagesLocked
        ? 'Percentages are locked after your first income entry. You can add, edit, or remove custom categories anytime.'
        : 'Percentage categories must total 100%. Custom categories can use optional defaults or amounts set per income.';

    return (
        <>
            <Head title="Savings Plan" />
            <Heading variant="small" title={plan.name} description={planDescription} />

            {!plan.hasIncome && (
                <BeforeChooseAlert note={pageGuidance.beforeChooseNote} />
            )}

            {plan.hasIncome && <PlanEditRulesPanel pageGuidance={pageGuidance} />}

            {fundBalances.length > 0 && (
                <div className="mt-6 rounded-lg border p-4">
                    <div className="flex items-center justify-between gap-4">
                        <div>
                            <h3 className="font-medium">Fund balances</h3>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Running totals from all locked income minus confirmed spending.
                            </p>
                        </div>
                        <Button variant="outline" size="sm" asChild>
                            <Link href={`/${teamSlug}/savings/spending`}>Record spending</Link>
                        </Button>
                    </div>
                    <ul className="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        {fundBalances.slice(0, 6).map((balance) => (
                            <li
                                key={balance.categoryId}
                                className="flex items-center justify-between gap-2 rounded-md bg-muted/40 px-3 py-2 text-sm"
                            >
                                <span>{balance.name}</span>
                                <span className="font-medium">{formatMoney(balance.remaining)}</span>
                            </li>
                        ))}
                    </ul>
                </div>
            )}

            {plan.hasIncome && (
                <Alert className="mt-6">
                    <AlertTriangle className="text-amber-600" />
                    <AlertTitle>Custom category changes affect all income</AlertTitle>
                    <AlertDescription>
                        Adding, editing, or removing a custom category updates this plan for
                        every income period — including locked periods. Past breakdowns and
                        spending tied to a removed category may no longer match.
                    </AlertDescription>
                </Alert>
            )}

            <Form
                action={`/${teamSlug}/savings/plan`}
                method="put"
                className="mt-6 space-y-6"
                onSubmit={handleSubmit}
            >
                {({ errors, processing }) => (
                    <>
                        {typeof errors.categories === 'string' && (
                            <InputError message={errors.categories} />
                        )}

                        <div className="space-y-4">
                    {rows.map((row, index) => {
                        const rowLocked = isPercentageRowLocked(row, plan.percentagesLocked);
                        const canRemove =
                            !rowLocked
                            && (plan.percentagesLocked
                                ? row.allocationType === 'deduction'
                                : rows.length > 1);

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

                                {rowLocked && (
                                    <>
                                        <input
                                            type="hidden"
                                            name={`categories[${index}][name]`}
                                            value={row.name}
                                        />
                                        {row.allocationType === 'percentage' && (
                                            <input
                                                type="hidden"
                                                name={`categories[${index}][percentage]`}
                                                value={row.percentage}
                                            />
                                        )}
                                    </>
                                )}

                                <div className="grid gap-4 sm:grid-cols-2">
                                    <div className="sm:col-span-2">
                                        <Label htmlFor={`category-name-${index}`}>Name</Label>
                                        <Input
                                            id={`category-name-${index}`}
                                            name={
                                                rowLocked
                                                    ? undefined
                                                    : `categories[${index}][name]`
                                            }
                                            value={row.name}
                                            onChange={(event) =>
                                                updateRow(index, { name: event.target.value })
                                            }
                                            disabled={rowLocked}
                                            readOnly={rowLocked}
                                            required
                                        />
                                        <InputError message={errors[`categories.${index}.name`]} />
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
                                                name={
                                                    rowLocked
                                                        ? undefined
                                                        : `categories[${index}][percentage]`
                                                }
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
                                                readOnly={rowLocked}
                                                required
                                            />
                                            <InputError
                                                message={errors[`categories.${index}.percentage`]}
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

                        <Button type="submit" disabled={processing}>
                            Save plan
                        </Button>
                        <button
                            ref={submitButtonRef}
                            type="submit"
                            className="hidden"
                            tabIndex={-1}
                            aria-hidden="true"
                        />
                    </>
                )}
            </Form>

            <Dialog open={confirmOpen} onOpenChange={setConfirmOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Save custom category changes?</DialogTitle>
                        <DialogDescription>
                            These changes apply to this savings plan for all income periods.
                            Locked periods, breakdowns, and spending linked to a removed custom
                            category may no longer match historical records.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => setConfirmOpen(false)}
                        >
                            Cancel
                        </Button>
                        <Button type="button" onClick={confirmSave}>
                            Save anyway
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}

SavingsPlanPage.layout = (props: SharedData) => ({
    breadcrumbs: [
        { title: 'Savings Plan', href: `/${props.currentTeam?.slug}/savings/plan` },
    ],
});
