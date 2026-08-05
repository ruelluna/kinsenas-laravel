export type CategoryAllocationType = 'percentage' | 'deduction';

export type DeductionMode = 'fixed' | 'percent_of_income';

export type SavingsCategory = {
    id: string;
    name: string;
    allocationType: CategoryAllocationType;
    percentage: string | null;
    deductionMode: DeductionMode | null;
    deductionValue: string | null;
    deductFromCategoryId: string | null;
    bankId: string | null;
    openingBalance: string | null;
};

export type SavingsPlanDetail = SavingsPlan & {
    categories: SavingsCategory[];
};

export type SavingsCategoryOpeningBalance = {
    id: string;
    openingBalance: string | null;
};

export type SavingsBankOption = {
    id: string;
    name: string;
    accountLabel: string | null;
    displayName: string;
    logoUrl: string | null;
    institutionId: string | null;
    institutionSlug: string | null;
    bankAccountGroupId: string | null;
    spaceRole: string | null;
    isActive: boolean;
};

export type RecipientTypeOption = {
    value: string;
    label: string;
};

export type IncomeBreakdownRow = {
    categoryId: string;
    name: string;
    allocationType?: CategoryAllocationType;
    percentage: string | null;
    amount: string | null;
    deductionMode?: DeductionMode | null;
    deductionValue?: string | null;
    deductFromCategoryId?: string | null;
    deductFromCategoryName?: string | null;
    deductionNote?: string | null;
};

export type IncomeCustomCategory = {
    categoryId: string;
    name: string;
    deductFromCategoryName: string | null;
    planDefaultAmount: string | null;
    periodAmount: string | null;
    hasPeriodOverride: boolean;
};

export type IncomeDistributionTodo = {
    id: string;
    categoryId: string;
    categoryName: string;
    bankId: string | null;
    bankDisplayName: string | null;
    bankLogoUrl: string | null;
    amount: string | null;
    status: 'pending' | 'completed';
    completedAt: string | null;
};

export type IncomeDistributionTodoProgress = {
    pendingCount: number;
    totalCount: number;
    complete: boolean;
};

export type IncomePeriodShowResponse = {
    data: IncomePeriod;
    breakdown: IncomeBreakdownRow[];
    customCategories: IncomeCustomCategory[];
    distributionTodos: IncomeDistributionTodo[];
    distributionTodoProgress: IncomeDistributionTodoProgress;
};

export type IncomeDistributionTodoCompleteResponse = {
    id: string;
    status: string;
    completedAt: string | null;
};

export type ReportTotals = {
    by_bank: Array<{
        bank_id: string;
        bank_name: string;
        logo_url: string | null;
        total: string;
        by_category: Array<{
            category_id: string;
            category_name: string;
            total: string;
        }>;
    }>;
    by_recipient: Array<{
        recipient_id: string;
        recipient_name: string;
        total: string;
    }>;
    fund_health: Array<{
        category_id: string;
        category_name: string;
        allocated: string;
        transferred: string;
        spent: string;
        remaining: string;
        percent_used: number;
        bank_id: string | null;
        bank_display_name: string | null;
        bank_logo_url: string | null;
    }>;
};

export type FundBalance = {
    categoryId: string;
    name: string;
    hint: string | null;
    isDefault: boolean;
    allocated: string | null;
    transferred: string | null;
    received: string | null;
    spent: string | null;
    remaining: string | null;
    openingBalance: string | null;
    canFund: boolean;
    percentUsed: number | null;
    bankId: string | null;
    bankDisplayName: string | null;
    bankLogoUrl: string | null;
};

export type FundSpend = {
    id: string;
    amount: string | null;
    description: string | null;
    status: string;
    spentOn: string;
    categoryName: string | null;
    categoryId: string;
    bankName: string | null;
    receiptUrl: string | null;
};

export type FundTransfer = {
    id: string;
    amount: string | null;
    description: string | null;
    status: string;
    transferredOn: string;
    fromCategoryName: string | null;
    toCategoryName: string | null;
    fromCategoryId: string;
    toCategoryId: string;
};

export type IncomePeriod = {
    id: string;
    label: string;
    amount: string | null;
    receivedOn: string;
    status: string;
    allocationsLocked: boolean;
};

export type SavingsPlan = {
    id: string;
    name: string;
    currency: string;
    isSharedWithTeam: boolean;
    allowEditingSpends: boolean;
    hasIncome: boolean;
    canDrawFromFunds: boolean;
};

/** @deprecated Use SavingsBankOption for API bank list payloads */
export type SavingsBank = SavingsBankOption;

export type SavingsRecipient = {
    id: string;
    type: string;
    typeLabel: string;
    name: string;
    notes: string | null;
};
