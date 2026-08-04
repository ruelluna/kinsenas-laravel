export type CategoryAllocationType = 'percentage' | 'deduction';

export type DeductionMode = 'fixed' | 'percent_of_income';

export type SavingsCategory = {
    id?: string;
    name: string;
    allocationType?: CategoryAllocationType;
    percentage?: string | null;
    deductionMode?: DeductionMode | null;
    deductionValue?: string | null;
    deductFromCategoryId?: string | null;
    deductFromCategoryName?: string | null;
    bankId?: string | null;
    openingBalance?: string | null;
};

export type SavingsPlan = {
    id: string;
    name: string;
    currency: string;
    isSharedWithTeam: boolean;
    allowEditingSpends: boolean;
    categories: SavingsCategory[];
    hasIncome: boolean;
    percentagesLocked: boolean;
    canDrawFromFunds: boolean;
};

export type FormulaTemplateCategory = {
    name: string;
    percentage: string;
    description: string | null;
};

export type FormulaTemplate = {
    id: string;
    name: string;
    slug: string;
    description: string | null;
    bestFor: string | null;
    videoEmbedUrl: string | null;
    categories: FormulaTemplateCategory[];
};

export type SavingsPlanPageGuidance = {
    chooserIntro: string | null;
    chooserVideoUrl: string | null;
    beforeChooseNote: string | null;
    afterIncomeRules: string | null;
    afterIncomeVideoUrl: string | null;
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
    fromBankName: string | null;
    toBankName: string | null;
    fromBankLogoUrl: string | null;
    toBankLogoUrl: string | null;
    crossesBanks: boolean;
};

export type FundSpend = {
    id: string;
    amount: string | null;
    description: string | null;
    status: string;
    spentOn: string;
    bankName: string | null;
    recipientName: string | null;
    categoryName: string | null;
    categoryId: string;
    recipientId: string | null;
    receiptImageUrl: string | null;
};

export type IncomeCustomCategory = {
    categoryId: string;
    name: string;
    deductFromCategoryName: string | null;
    planDefaultAmount: string | null;
    periodAmount: string | null;
    hasPeriodOverride: boolean;
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

export type IncomePeriodSummary = {
    id: string;
    name: string;
    periodStart: string;
    amount: string | null;
    deleteBlockReason?: string | null;
};

export type IncomeDistributionTodoProgress = {
    pendingCount: number;
    totalCount: number;
    complete: boolean;
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

export type IncomePlanCategory = {
    id: string;
    name: string;
    allocationType: CategoryAllocationType;
    percentage: string | null;
    deductionMode?: DeductionMode | null;
    deductionValue?: string | null;
};

export type IncomePeriodTableRow = IncomePeriodSummary & {
    categoryAmounts: Record<string, string | null>;
    distributionTodoProgress: IncomeDistributionTodoProgress;
};

export type IncomeFundSummary = {
    categorySpent: Record<string, string | null>;
    categoryRemaining: Record<string, string | null>;
};

export type IncomePeriod = IncomePeriodSummary;

export type Bank = {
    id: string;
    name: string;
    accountLabel: string | null;
    displayName: string;
    isActive: boolean;
    logoUrl?: string | null;
    institutionId?: string | null;
    institutionSlug?: string | null;
    bankAccountGroupId?: string | null;
    spaceRole?: 'main' | 'savings_space' | null;
};

export type BankInstitutionSavingsSpaces = {
    max: number;
    mainLabel: string;
    spaceLabelPrefix: string;
};

export type BankInstitution = {
    id: string;
    name: string;
    logoUrl: string | null;
    type: 'bank' | 'e_wallet';
    features?: Record<string, unknown> | null;
    savingsSpaces?: BankInstitutionSavingsSpaces | null;
};

export type BankBalance = {
    bankId: string;
    bankName: string;
    logoUrl: string | null;
    total: string;
    byCategory: Array<{
        categoryId: string;
        categoryName: string;
        total: string;
    }>;
};

export type BankOption = {
    id: string;
    name: string;
    accountLabel?: string | null;
    displayName?: string;
    logoUrl?: string | null;
    institutionSlug?: string | null;
    bankAccountGroupId?: string | null;
    spaceRole?: 'main' | 'savings_space' | null;
};

export type CategoryBankMap = Record<string, string | null>;

export type Recipient = {
    id: string;
    type: string;
    typeLabel: string;
    name: string;
    notes: string | null;
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
