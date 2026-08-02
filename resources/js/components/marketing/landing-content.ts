export const SAMPLE_INCOME = 15000;

export type LandingBucket = {
    label: string;
    percentage: number;
    colorIndex: 1 | 2 | 3 | 4 | 5 | 6;
};

export const HERO_BUCKETS: LandingBucket[] = [
    { label: 'Bills', percentage: 45, colorIndex: 1 },
    { label: 'Savings', percentage: 15, colorIndex: 6 },
    { label: 'Giving', percentage: 10, colorIndex: 3 },
    { label: 'Family', percentage: 10, colorIndex: 4 },
    { label: 'Emergency', percentage: 10, colorIndex: 5 },
    { label: 'Goals', percentage: 10, colorIndex: 2 },
];

export const FORMULA_BUCKETS: LandingBucket[] = [
    { label: 'Needs', percentage: 45, colorIndex: 1 },
    { label: 'Savings', percentage: 15, colorIndex: 6 },
    { label: 'Giving', percentage: 10, colorIndex: 3 },
    { label: 'Family', percentage: 10, colorIndex: 4 },
    { label: 'Emergency', percentage: 10, colorIndex: 5 },
    { label: 'Goals', percentage: 10, colorIndex: 2 },
];

export const ALLOCATION_BG_CLASSES = {
    1: 'bg-allocation-1',
    2: 'bg-allocation-2',
    3: 'bg-allocation-3',
    4: 'bg-allocation-4',
    5: 'bg-allocation-5',
    6: 'bg-allocation-6',
} as const;

export const ALLOCATION_BORDER_CLASSES = {
    1: 'border-l-allocation-1',
    2: 'border-l-allocation-2',
    3: 'border-l-allocation-3',
    4: 'border-l-allocation-4',
    5: 'border-l-allocation-5',
    6: 'border-l-allocation-6',
} as const;

export const ALLOCATION_TINT_CLASSES = {
    1: 'bg-allocation-1/12',
    2: 'bg-allocation-2/12',
    3: 'bg-allocation-3/12',
    4: 'bg-allocation-4/12',
    5: 'bg-allocation-5/12',
    6: 'bg-allocation-6/12',
} as const;

export const ALLOCATION_TEXT_CLASSES = {
    1: 'text-allocation-1',
    2: 'text-allocation-2',
    3: 'text-allocation-3',
    4: 'text-allocation-4',
    5: 'text-allocation-5',
    6: 'text-allocation-6',
} as const;

export const HERO_BUCKET_CARD_CLASSES = {
    1: {
        surface: 'bg-allocation-1/95 border-allocation-1',
        label: 'text-white',
        muted: 'text-white/75',
    },
    2: {
        surface: 'bg-allocation-2/95 border-allocation-2',
        label: 'text-white',
        muted: 'text-white/75',
    },
    3: {
        surface: 'bg-allocation-3/95 border-allocation-3',
        label: 'text-black/85',
        muted: 'text-black/65',
    },
    4: {
        surface: 'bg-allocation-4/95 border-allocation-4',
        label: 'text-white',
        muted: 'text-white/75',
    },
    5: {
        surface: 'bg-allocation-5/95 border-allocation-5',
        label: 'text-white',
        muted: 'text-white/75',
    },
    6: {
        surface: 'bg-allocation-6/95 border-allocation-6',
        label: 'text-white',
        muted: 'text-white/75',
    },
} as const;

export const PAIN_POINTS = [
    'May sweldo, pero parang dumaan lang.',
    'Nakapag-save sana, kaso nagalaw.',
    'Hindi malinaw kung magkano dapat sa bills, savings, giving, or goals.',
    'Kapag may biglang kailangan, doon na napupunta lahat.',
];

export const HOW_IT_WORKS_STEPS = [
    {
        title: 'Split your income',
        description:
            'Enter your sahod, freelance pay, allowance, or business income. Kinsenas divides it using a formula you choose.',
    },
    {
        title: 'Set aside with intention',
        description:
            'Set up fund buckets like savings, bills, family, giving, emergency fund, and personal goals.',
    },
    {
        title: 'Track what you actually moved',
        description:
            'Mark transfers when you send money to your bank, wallet, family member, church, or savings account.',
    },
];

export const OUTCOME_BULLETS = [
    'You know what each payday is for.',
    'You can save without overthinking.',
    'You can help others without losing your plan.',
    'You can enjoy spending because the important parts are already set aside.',
];

export const ENCRYPTION_TRUST_POINTS = [
    'Your income, allocations, transfers, and spending amounts are encrypted in your personal vault.',
    'Kinsenas staff and platform admins cannot read your financial data — not in the app, not in the database.',
    'Only you can unlock and view your amounts when you sign in and open your vault.',
];

export const FOOTER_TAGLINES = [
    'Bago maubos, itabi na.',
    'Sweldo with a plan.',
    'Every payday, may direction.',
    'Hindi lang budget. Sistema sa sweldo.',
    'Para hindi na "saan napunta?"',
];

export function bucketAmount(income: number, percentage: number): number {
    return (income * percentage) / 100;
}
