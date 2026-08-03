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

export type SavingsBank = {
    id: string;
    name: string;
    displayName: string;
    logoUrl: string | null;
};

export type SavingsRecipient = {
    id: string;
    name: string;
    accountNumber: string | null;
    bankName: string | null;
};
