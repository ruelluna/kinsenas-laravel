<?php

namespace Database\Seeders\Support;

class DemoAccountHistory
{
    /**
     * @return list<array{
     *     income: array{name: string, amount: string, period_start: string},
     *     spends: list<array{category: string, amount: string, description: string, day: int}>,
     *     transfers: list<array{from: string, to: string, amount: string, description: string, day: int}>
     * }>
     */
    public static function months(): array
    {
        return [
            [
                'income' => [
                    'name' => 'March salary',
                    'amount' => '48000.00',
                    'period_start' => '2026-03-01',
                ],
                'spends' => [
                    ['category' => 'Everyday Fund', 'amount' => '8500.00', 'description' => 'Groceries — SM Hypermarket', 'day' => 5],
                    ['category' => 'Everyday Fund', 'amount' => '1500.00', 'description' => 'MRT load and commute', 'day' => 8],
                    ['category' => 'Everyday Fund', 'amount' => '4500.00', 'description' => 'Rent share', 'day' => 10],
                    ['category' => 'Enjoyment', 'amount' => '350.00', 'description' => 'Coffee with friends', 'day' => 12],
                    ['category' => 'Enjoyment', 'amount' => '900.00', 'description' => 'Dining out — weekend', 'day' => 18],
                    ['category' => 'Utility', 'amount' => '1200.00', 'description' => 'PLDT internet bill', 'day' => 15],
                    ['category' => 'Tithe', 'amount' => '4800.00', 'description' => 'Church offering', 'day' => 3],
                    ['category' => 'Educational', 'amount' => '800.00', 'description' => 'Udemy course — Excel basics', 'day' => 22],
                ],
                'transfers' => [
                    ['from' => 'Everyday Fund', 'to' => 'Savings', 'amount' => '3500.00', 'description' => 'Move to long-term savings', 'day' => 25],
                ],
            ],
            [
                'income' => [
                    'name' => 'April salary',
                    'amount' => '50000.00',
                    'period_start' => '2026-04-01',
                ],
                'spends' => [
                    ['category' => 'Everyday Fund', 'amount' => '9200.00', 'description' => 'Groceries — Puregold', 'day' => 4],
                    ['category' => 'Everyday Fund', 'amount' => '1500.00', 'description' => 'MRT load and commute', 'day' => 7],
                    ['category' => 'Everyday Fund', 'amount' => '4500.00', 'description' => 'Rent share', 'day' => 10],
                    ['category' => 'Enjoyment', 'amount' => '450.00', 'description' => 'Movie night', 'day' => 14],
                    ['category' => 'Enjoyment', 'amount' => '750.00', 'description' => 'Birthday dinner', 'day' => 20],
                    ['category' => 'Utility', 'amount' => '980.00', 'description' => 'Meralco bill', 'day' => 16],
                    ['category' => 'Tithe', 'amount' => '5000.00', 'description' => 'Church offering', 'day' => 3],
                    ['category' => 'Emergency Fund', 'amount' => '650.00', 'description' => 'Pharmacy — medicine', 'day' => 24],
                ],
                'transfers' => [
                    ['from' => 'Everyday Fund', 'to' => 'Savings', 'amount' => '4000.00', 'description' => 'Monthly savings move', 'day' => 26],
                    ['from' => 'Everyday Fund', 'to' => 'Emergency Fund', 'amount' => '2000.00', 'description' => 'Top up emergency fund', 'day' => 28],
                ],
            ],
            [
                'income' => [
                    'name' => 'May salary',
                    'amount' => '51000.00',
                    'period_start' => '2026-05-01',
                ],
                'spends' => [
                    ['category' => 'Everyday Fund', 'amount' => '8800.00', 'description' => 'Groceries — Robinsons', 'day' => 6],
                    ['category' => 'Everyday Fund', 'amount' => '1500.00', 'description' => 'MRT load and commute', 'day' => 8],
                    ['category' => 'Everyday Fund', 'amount' => '4500.00', 'description' => 'Rent share', 'day' => 10],
                    ['category' => 'Enjoyment', 'amount' => '600.00', 'description' => 'Spa day', 'day' => 17],
                    ['category' => 'Utility', 'amount' => '1500.00', 'description' => 'Home repair — faucet', 'day' => 19],
                    ['category' => 'Tithe', 'amount' => '5100.00', 'description' => 'Church offering', 'day' => 3],
                ],
                'transfers' => [
                    ['from' => 'Everyday Fund', 'to' => 'Savings', 'amount' => '4500.00', 'description' => 'Monthly savings move', 'day' => 27],
                ],
            ],
            [
                'income' => [
                    'name' => 'June salary',
                    'amount' => '49500.00',
                    'period_start' => '2026-06-01',
                ],
                'spends' => [
                    ['category' => 'Everyday Fund', 'amount' => '9000.00', 'description' => 'Groceries — SM Hypermarket', 'day' => 5],
                    ['category' => 'Everyday Fund', 'amount' => '1500.00', 'description' => 'MRT load and commute', 'day' => 9],
                    ['category' => 'Everyday Fund', 'amount' => '4500.00', 'description' => 'Rent share', 'day' => 10],
                    ['category' => 'Enjoyment', 'amount' => '1100.00', 'description' => 'Staycation day trip', 'day' => 21],
                    ['category' => 'Utility', 'amount' => '1100.00', 'description' => 'PLDT internet bill', 'day' => 14],
                    ['category' => 'Tithe', 'amount' => '4950.00', 'description' => 'Church offering', 'day' => 3],
                    ['category' => 'Educational', 'amount' => '1200.00', 'description' => 'Book — personal finance', 'day' => 23],
                ],
                'transfers' => [
                    ['from' => 'Everyday Fund', 'to' => 'Savings', 'amount' => '3800.00', 'description' => 'Monthly savings move', 'day' => 25],
                ],
            ],
            [
                'income' => [
                    'name' => 'July salary',
                    'amount' => '52000.00',
                    'period_start' => '2026-07-01',
                ],
                'spends' => [
                    ['category' => 'Everyday Fund', 'amount' => '9500.00', 'description' => 'Groceries — Puregold', 'day' => 4],
                    ['category' => 'Everyday Fund', 'amount' => '1500.00', 'description' => 'MRT load and commute', 'day' => 7],
                    ['category' => 'Everyday Fund', 'amount' => '4500.00', 'description' => 'Rent share', 'day' => 10],
                    ['category' => 'Enjoyment', 'amount' => '800.00', 'description' => 'Concert tickets', 'day' => 16],
                    ['category' => 'Utility', 'amount' => '1350.00', 'description' => 'Meralco bill', 'day' => 18],
                    ['category' => 'Tithe', 'amount' => '5200.00', 'description' => 'Church offering', 'day' => 3],
                    ['category' => 'Emergency Fund', 'amount' => '900.00', 'description' => 'Dental checkup', 'day' => 22],
                ],
                'transfers' => [
                    ['from' => 'Everyday Fund', 'to' => 'Savings', 'amount' => '5000.00', 'description' => 'Monthly savings move', 'day' => 26],
                    ['from' => 'Everyday Fund', 'to' => 'Emergency Fund', 'amount' => '2500.00', 'description' => 'Top up emergency fund', 'day' => 28],
                ],
            ],
            [
                'income' => [
                    'name' => 'August salary',
                    'amount' => '50000.00',
                    'period_start' => '2026-08-01',
                ],
                'spends' => [
                    ['category' => 'Everyday Fund', 'amount' => '8700.00', 'description' => 'Groceries — SM Hypermarket', 'day' => 5],
                    ['category' => 'Everyday Fund', 'amount' => '1500.00', 'description' => 'MRT load and commute', 'day' => 8],
                    ['category' => 'Everyday Fund', 'amount' => '4500.00', 'description' => 'Rent share', 'day' => 10],
                    ['category' => 'Enjoyment', 'amount' => '550.00', 'description' => 'Coffee shop visits', 'day' => 13],
                    ['category' => 'Utility', 'amount' => '1050.00', 'description' => 'PLDT internet bill', 'day' => 15],
                    ['category' => 'Tithe', 'amount' => '5000.00', 'description' => 'Church offering', 'day' => 3],
                ],
                'transfers' => [
                    ['from' => 'Everyday Fund', 'to' => 'Savings', 'amount' => '4200.00', 'description' => 'Monthly savings move', 'day' => 12],
                ],
            ],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function categoryBankAssignments(): array
    {
        return [
            'payroll' => [
                'Everyday Fund',
                'Utility',
                'Enjoyment',
                'Educational',
            ],
            'gosave' => [
                'Savings',
                'Emergency Fund',
                'Tithe',
            ],
        ];
    }
}
