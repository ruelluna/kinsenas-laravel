export type SavingsCategory = {
    id?: string;
    name: string;
    percentage: string;
};

export type SavingsPlan = {
    id: string;
    name: string;
    currency: string;
    isSharedWithTeam: boolean;
    categories: SavingsCategory[];
    hasLockedIncome: boolean;
};

export type FormulaTemplate = {
    id: string;
    name: string;
    slug: string;
    description: string | null;
    categories: SavingsCategory[];
};

export type IncomePeriod = {
    id: string;
    periodStart: string;
    amount: string | null;
    isLocked: boolean;
    allocations: Array<{
        categoryName: string | null;
        amount: string | null;
    }>;
};

export type Bank = {
    id: string;
    name: string;
    accountLabel: string | null;
    isActive: boolean;
};

export type Recipient = {
    id: string;
    type: string;
    typeLabel: string;
    name: string;
    notes: string | null;
};

export type Transfer = {
    id: string;
    amount: string | null;
    status: string;
    transferredOn: string;
    bankName: string | null;
    recipientName: string | null;
    categoryName: string | null;
    periodStart: string | null;
};

export type ReportTotals = {
    by_bank: Array<{ bank_id: string; bank_name: string; total: string }>;
    by_recipient: Array<{ recipient_id: string; recipient_name: string; total: string }>;
    by_category: Array<{ category_id: string; category_name: string; total: string }>;
};
