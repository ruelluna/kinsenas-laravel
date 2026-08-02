import { Form, Head, usePage } from '@inertiajs/react';
import { AlertTriangle, ArrowLeft, Plus, Trash2 } from 'lucide-react';
import { useMemo, useRef, useState } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import AddFundBalanceModal from '@/components/savings/add-fund-balance-modal';
import type { ExistingFundTarget } from '@/components/savings/add-fund-balance-modal';
import CategoryBankSelect from '@/components/savings/category-bank-select';
import FundBalancesSection from '@/components/savings/fund-balances-section';
import { PlanEditRulesPanel } from '@/components/savings/plan-guidance-panels';
import SavingsPlanTemplatePicker from '@/components/savings/plan-template-picker';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
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
import type { SharedData } from '@/types';
import type {
    CategoryAllocationType,
    DeductionMode,
    FormulaTemplate,
    FundBalance,
    BankOption,
    SavingsCategory,
    SavingsPlan,
    SavingsPlanPageGuidance,
} from '@/types/savings';


type Props = {
    plan: SavingsPlan | null;
    templates: FormulaTemplate[];
    fundBalances: FundBalance[];
    pageGuidance: SavingsPlanPageGuidance;
    teamBanks: BankOption[];
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
    bankId: string;
    openingBalance: string;
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
        bankId: '',
        openingBalance: '',
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
        bankId: '',
        openingBalance: '',
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
            bankId: category.bankId ?? '',
            openingBalance: category.openingBalance ?? '',
        };
    });
}

function selectClassName(): string {
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

export default function SavingsPlanPage({ plan, templates, fundBalances, pageGuidance, teamBanks }: Props) {
    const page = usePage<SharedData & { flash?: { error?: string } }>();
    const { currentTeam } = page.props;
    const teamSlug = currentTeam?.slug ?? '';
    const flashError = page.props.flash?.error;

    if (!plan) {
        return (
            <div data-tour="plan-main">
                <Head title="Savings Plan" />
                {flashError && <PlanRedirectAlert message={flashError} />}
                <Heading
                    variant="small"
                    title="Choose a savings formula"
                    description="Compare each plan below and pick the split that fits how you manage money."
                />
                <SavingsPlanTemplatePicker
                    templates={templates}
                    pageGuidance={pageGuidance}
                    teamSlug={teamSlug}
                    hasBanks={teamBanks.length > 0}
                />
            </div>
        );
    }

    return (
        <>
            {flashError && <PlanRedirectAlert message={flashError} />}
            <SavingsPlanEditor
                key={plan.categories
                    .map(
                        (category) =>
                            `${category.id ?? category.name}-${category.percentage}-${category.allocationType}`,
                    )
                    .join('|')}
                plan={plan}
                teamSlug={teamSlug}
                fundBalances={fundBalances}
                pageGuidance={pageGuidance}
                teamBanks={teamBanks}
            />
        </>
    );
}

function PlanRedirectAlert({ message }: { message: string }) {
    return (
        <Alert variant="destructive" className="mb-4">
            <AlertTitle>Choose a savings plan first</AlertTitle>
            <AlertDescription>{message}</AlertDescription>
        </Alert>
    );
}

function SavingsPlanEditor({
    plan,
    teamSlug,
    fundBalances,
    pageGuidance,
    teamBanks,
}: {
    plan: SavingsPlan;
    teamSlug: string;
    fundBalances: FundBalance[];
    pageGuidance: SavingsPlanPageGuidance;
    teamBanks: BankOption[];
}) {
    const [rows, setRows] = useState<CategoryRow[]>(() => rowsFromPlan(plan.categories));
    const [confirmOpen, setConfirmOpen] = useState(false);
    const [chooseFormulaOpen, setChooseFormulaOpen] = useState(false);
    const [fundModalOpen, setFundModalOpen] = useState(false);
    const [selectedFundTarget, setSelectedFundTarget] = useState<ExistingFundTarget | null>(null);
    const submitButtonRef = useRef<HTMLButtonElement>(null);
    const skipCustomConfirmRef = useRef(false);

    const existingFundForRow = (row: CategoryRow): string | null => {
        if (!row.id) {
            return row.openingBalance || null;
        }

        const balance = fundBalances.find((item) => item.categoryId === row.id);

        return balance?.openingBalance ?? row.openingBalance ?? null;
    };

    const openExistingFundModal = (row: CategoryRow) => {
        if (!row.id) {
            return;
        }

        setSelectedFundTarget({
            categoryId: row.id,
            name: row.name,
            openingBalance: existingFundForRow(row),
        });
        setFundModalOpen(true);
    };

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
        ? 'Percentages are locked after your first income entry. You can add, edit, or remove custom fund buckets anytime.'
        : 'Percentage fund buckets must total 100%. Custom fund buckets can use optional defaults or amounts set per income.';

    return (
        <div data-tour="plan-main">
            <Head title="Savings Plan" />
            <div className="flex flex-wrap items-start justify-between gap-4">
                <Heading variant="small" title={plan.name} description={planDescription} />
                {!plan.hasIncome && (
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() => setChooseFormulaOpen(true)}
                    >
                        <ArrowLeft className="size-4" />
                        Choose a different formula
                    </Button>
                )}
            </div>

            <Form
                action={`/${teamSlug}/savings/plan`}
                method="put"
                className="mt-6 space-y-6"
                onSubmit={handleSubmit}
            >
                {({ errors, processing }) => (
                    <>
                        {fundBalances.length > 0 && (
                            <FundBalancesSection
                                title="Fund balances"
                                description={
                                    plan.hasLockedIncome
                                        ? 'Existing funds plus locked income, minus transfers and spending.'
                                        : 'Add existing savings to any fund bucket anytime. Locked income adds payday allocations on top.'
                                }
                                fundBalances={fundBalances}
                                spendHref={`/${teamSlug}/savings/spending`}
                                canDrawFromFunds={plan.canDrawFromFunds}
                                limit={6}
                                bordered
                            />
                        )}

                        {plan.hasIncome && <PlanEditRulesPanel pageGuidance={pageGuidance} />}

                        {plan.hasIncome && (
                            <Alert variant="warning">
                                <AlertTriangle />
                                <AlertTitle>Custom fund bucket changes affect all income</AlertTitle>
                                <AlertDescription>
                                    Adding, editing, or removing a custom fund bucket updates this plan for
                                    every income period — including locked periods. Past breakdowns and
                                    spending tied to a removed fund bucket may no longer match.
                                </AlertDescription>
                            </Alert>
                        )}

                        {typeof errors.categories === 'string' && (
                            <InputError message={errors.categories} />
                        )}

                        <div className="grid gap-4 lg:grid-cols-3">
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
                                        Fund bucket {index + 1}
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
                                                className={selectClassName()}
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
                                                    className={selectClassName()}
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
                                                    className={selectClassName()}
                                                    value={row.deductFromIndex}
                                                    onChange={(event) =>
                                                        updateRow(index, {
                                                            deductFromIndex: event.target.value,
                                                        })
                                                    }
                                                    disabled={rowLocked}
                                                    required
                                                >
                                                    <option value="">Select a fund bucket</option>
                                                    {percentageRows.map(
                                                        ({ row: sourceRow, index: sourceIndex }) => (
                                                            <option
                                                                key={`${sourceRow.key}-${sourceIndex}`}
                                                                value={String(sourceIndex)}
                                                                disabled={sourceIndex === index}
                                                            >
                                                                {sourceRow.name ||
                                                                    `Fund bucket ${sourceIndex + 1}`}
                                                            </option>
                                                        ),
                                                    )}
                                                </select>
                                            </div>
                                        </>
                                    )}
                                </div>

                                <div className="mt-4">
                                    <CategoryBankSelect
                                        banks={teamBanks}
                                        selectedId={row.bankId}
                                        onChange={(bankId) => updateRow(index, { bankId })}
                                        namePrefix={`categories[${index}]`}
                                    />
                                </div>

                                {row.id && (
                                    <div className="mt-4 space-y-2 border-t pt-4">
                                        {(() => {
                                            const existingFund = existingFundForRow(row);
                                            const hasExistingFund =
                                                existingFund !== null
                                                && parseFloat(existingFund) > 0;

                                            return hasExistingFund ? (
                                                <p className="text-sm text-muted-foreground">
                                                    Existing fund:{' '}
                                                    <span className="font-medium text-foreground">
                                                        {formatMoney(existingFund)}
                                                    </span>
                                                </p>
                                            ) : null;
                                        })()}
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            className="w-full"
                                            onClick={() => openExistingFundModal(row)}
                                        >
                                            Add Existing Fund
                                        </Button>
                                    </div>
                                )}
                            </div>
                        );
                    })}
                        </div>

                        <Button type="button" variant="outline" onClick={addRow}>
                            <Plus className="size-4" />
                            {plan.percentagesLocked ? 'Add custom fund bucket' : 'Add fund bucket'}
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

                        <label className="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                name="allow_editing_spends"
                                value="1"
                                defaultChecked={plan.allowEditingSpends}
                            />
                            Allow editing and deleting recorded spending
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

            <AddFundBalanceModal
                open={fundModalOpen}
                onOpenChange={setFundModalOpen}
                target={selectedFundTarget}
            />

            <Dialog open={confirmOpen} onOpenChange={setConfirmOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Save custom fund bucket changes?</DialogTitle>
                        <DialogDescription>
                            These changes apply to this savings plan for all income periods.
                            Locked periods, breakdowns, and spending linked to a removed custom
                            fund bucket may no longer match historical records.
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

            <Dialog open={chooseFormulaOpen} onOpenChange={setChooseFormulaOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Choose a different formula?</DialogTitle>
                        <DialogDescription>
                            This removes your current plan setup and returns you to the formula
                            chooser. You have not entered income yet, so you can pick another split
                            without losing historical data.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => setChooseFormulaOpen(false)}
                        >
                            Keep this plan
                        </Button>
                        <Form
                            action={`/${teamSlug}/savings/plan`}
                            method="delete"
                            options={{ preserveScroll: true }}
                        >
                            {({ processing }) => (
                                <Button type="submit" variant="destructive" disabled={processing}>
                                    Choose another formula
                                </Button>
                            )}
                        </Form>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    );
}

SavingsPlanPage.layout = (props: SharedData) => ({
    breadcrumbs: [
        { title: 'Savings Plan', href: `/${props.currentTeam?.slug}/savings/plan` },
    ],
});
