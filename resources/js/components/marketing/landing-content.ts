import { formatMoney } from '@/lib/format-money';
import { MARKETING_BANK_LOGOS } from '@/lib/brand';

export const SAMPLE_INCOME = 15000;

export type LandingBucket = {
    label: string;
    percentage: number;
    colorIndex: 1 | 2 | 3 | 4 | 5 | 6;
};

export const HERO_BUCKETS: LandingBucket[] = [
    { label: 'Essential Bills', percentage: 45, colorIndex: 1 },
    { label: 'Savings', percentage: 15, colorIndex: 6 },
    { label: 'Emergency', percentage: 10, colorIndex: 5 },
    { label: 'Family', percentage: 10, colorIndex: 3 },
    { label: 'Giving', percentage: 10, colorIndex: 4 },
    { label: 'Goals', percentage: 10, colorIndex: 2 },
];

export const HERO_DEMO_BUCKETS = [
    {
        label: 'Savings (15%)',
        amount: 2250,
        accentClass: 'bg-glow',
        textClass: 'text-glow',
    },
    {
        label: 'Emergency (10%)',
        amount: 1500,
        accentClass: 'bg-teal',
        textClass: 'text-teal',
    },
    {
        label: 'Family (10%)',
        amount: 1500,
        accentClass: 'bg-gold',
        textClass: 'text-gold',
    },
    {
        label: 'Giving (10%)',
        amount: 1500,
        accentClass: 'bg-clay',
        textClass: 'text-clay',
    },
] as const;

export type ComparisonLine = {
    icon: string;
    label: string;
    amount: number;
    description: string;
    amountClass: string;
};

export const USUAL_PAYDAY_LINES: ComparisonLine[] = [
    {
        icon: 'receipt',
        label: 'Bills & essentials',
        amount: 6750,
        description: 'Rent, utilities, groceries, transport',
        amountClass: 'text-muted-foreground',
    },
    {
        icon: 'users',
        label: 'Family padala',
        amount: 3000,
        description: 'Reactive support when relatives ask',
        amountClass: 'text-gold',
    },
    {
        icon: 'credit-card',
        label: 'Debt payments',
        amount: 2250,
        description: 'Credit card, loans, utang minimums',
        amountClass: 'text-clay',
    },
    {
        icon: 'shopping-bag',
        label: 'Impulse spending',
        amount: 2250,
        description: 'Food delivery, sales, “treat yourself”',
        amountClass: 'text-lilac',
    },
    {
        icon: 'wallet',
        label: 'Savings',
        amount: 0,
        description: 'Whatever is left — usually nothing',
        amountClass: 'text-destructive',
    },
];

export const KINSENAS_PAYDAY_LINES: ComparisonLine[] = [
    {
        icon: 'piggy-bank',
        label: 'Savings first',
        amount: 2250,
        description: 'Pay yourself before anything else',
        amountClass: 'text-glow',
    },
    {
        icon: 'heart',
        label: 'Planned giving',
        amount: 1500,
        description: 'Family support as a fixed bucket',
        amountClass: 'text-gold',
    },
    {
        icon: 'receipt',
        label: 'Bills reserved',
        amount: 6750,
        description: 'Essentials covered immediately',
        amountClass: 'text-muted-foreground',
    },
    {
        icon: 'trending-up',
        label: 'Debt pay-down',
        amount: 1500,
        description: 'Extra principal, not just minimums',
        amountClass: 'text-teal',
    },
    {
        icon: 'shopping-bag',
        label: 'Guilt-free spending',
        amount: 3000,
        description: 'What is left is yours to enjoy',
        amountClass: 'text-lilac',
    },
];

export type BankAccountDemo = {
    name: string;
    type: string;
    total: number;
    logoUrl: string;
    funds: { label: string; amount: number; textClass: string }[];
};

export const BANK_ACCOUNTS: BankAccountDemo[] = [
    {
        name: 'BPI Savings',
        type: 'Bank',
        total: 3750,
        logoUrl: MARKETING_BANK_LOGOS.bpi,
        funds: [
            { label: 'Savings', amount: 2250, textClass: 'text-glow' },
            { label: 'Emergency', amount: 1500, textClass: 'text-teal' },
        ],
    },
    {
        name: 'BDO',
        type: 'Bank',
        total: 6750,
        logoUrl: MARKETING_BANK_LOGOS.bdo,
        funds: [
            {
                label: 'Bills & essentials',
                amount: 6750,
                textClass: 'text-primary',
            },
        ],
    },
    {
        name: 'GCash',
        type: 'E-wallet',
        total: 3000,
        logoUrl: MARKETING_BANK_LOGOS.gcash,
        funds: [
            { label: 'Family padala', amount: 1500, textClass: 'text-gold' },
            { label: 'Giving', amount: 1500, textClass: 'text-clay' },
        ],
    },
    {
        name: 'Maya',
        type: 'E-wallet',
        total: 1500,
        logoUrl: MARKETING_BANK_LOGOS.maya,
        funds: [{ label: 'Goals', amount: 1500, textClass: 'text-lilac' }],
    },
];

export const LOOP_STEPS = [
    {
        number: 1,
        title: 'Choose formula',
        description:
            'Select 70/20/10, TRC, or create a custom bucket system.',
        badgeClass: 'bg-primary text-primary-foreground',
    },
    {
        number: 2,
        title: 'Record payday',
        description:
            'Input your net pay. We instantly calculate the distribution.',
        badgeClass: 'bg-gold text-on-accent',
    },
    {
        number: 3,
        title: 'Confirm transfers',
        description:
            'Move your funds to their respective banks or e-wallets and check them off.',
        badgeClass: 'bg-teal text-on-accent',
    },
] as const;

export const FORMULA_CARDS = [
    {
        title: 'Abundant 70/20/10',
        description:
            'A simple, powerful split for those prioritizing wealth building and debt freedom.',
        hoverBorder: 'hover:border-gold/40',
        hoverTitle: 'group-hover:text-gold',
        badgeClass: 'bg-gold/15 text-gold',
        lines: [
            { label: 'Living Expenses', pct: '70%' },
            { label: 'Savings', pct: '20%' },
            { label: 'Giving', pct: '10%' },
        ],
    },
    {
        title: 'TRC Seven Buckets',
        description:
            'The comprehensive Filipino strategy for long-term abundance and legacy.',
        hoverBorder: 'hover:border-teal/40',
        hoverTitle: 'group-hover:text-teal',
        badgeClass: 'bg-teal/15 text-teal',
        lines: [
            { label: 'Tithe & Charity', pct: '10%' },
            { label: 'Education', pct: '20%' },
            { label: 'Emergency', pct: '10%' },
        ],
    },
] as const;

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

export function bucketAmount(income: number, percentage: number): number {
    return (income * percentage) / 100;
}

export const SAMPLE_SAVED_AMOUNT = formatMoney(3750);
