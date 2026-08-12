import type {
    CategoryAllocationType,
    DeductionMode,
} from '@/types/savings';

type CategoryAllocationFields = {
    allocationType: CategoryAllocationType;
    percentage?: string | null;
    deductionMode?: DeductionMode | null;
    deductionValue?: string | null;
};

export function formatPercentLabel(value: string): string {
    const numeric = parseFloat(value);

    if (!Number.isFinite(numeric)) {
        return '';
    }

    return `${Math.round(numeric)}%`;
}

export function categoryAllocationLabel(
    category: CategoryAllocationFields,
): string | null {
    if (category.allocationType === 'deduction') {
        if (
            category.deductionMode === 'percent_of_income' &&
            category.deductionValue
        ) {
            return formatPercentLabel(category.deductionValue);
        }

        return 'Custom';
    }

    return category.percentage !== null && category.percentage !== undefined
        ? formatPercentLabel(category.percentage)
        : null;
}

export function categoryTitleWithAllocation(
    name: string,
    category: CategoryAllocationFields,
): string {
    const label = categoryAllocationLabel(category);

    return label !== null ? `${name} · ${label}` : name;
}
