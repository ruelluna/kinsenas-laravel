import { Head, Link, usePage } from '@inertiajs/react';
import { Info, Plus } from 'lucide-react';
import { useMemo, useState } from 'react';
import Heading from '@/components/heading';
import AddBankModal from '@/components/savings/add-bank-modal';
import { BankLogo } from '@/components/savings/bank-select';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { formatBankOptionLabel } from '@/lib/format-bank-label';
import { formatMoney } from '@/lib/format-money';
import type { SharedData } from '@/types';
import type { Bank, BankBalance, BankInstitution } from '@/types/savings';

type Props = {
    banks: Bank[];
    institutions: BankInstitution[];
    bankBalances: BankBalance[];
};

type BankGroup = {
    key: string;
    label: string;
    banks: Bank[];
    total: string | null;
};

export default function BanksIndex({ banks, institutions, bankBalances }: Props) {
    const { currentTeam } = usePage<SharedData>().props;
    const teamSlug = currentTeam?.slug ?? '';
    const [addModalOpen, setAddModalOpen] = useState(false);

    const balanceForBank = (bankId: string) => bankBalances.find((balance) => balance.bankId === bankId);

    const bankGroups = useMemo(() => {
        const groups = new Map<string, BankGroup>();
        const ungrouped: Bank[] = [];

        for (const bank of banks) {
            if (bank.bankAccountGroupId) {
                const existing = groups.get(bank.bankAccountGroupId);

                if (existing) {
                    existing.banks.push(bank);
                    continue;
                }

                groups.set(bank.bankAccountGroupId, {
                    key: bank.bankAccountGroupId,
                    label: bank.name,
                    banks: [bank],
                    total: null,
                });

                continue;
            }

            ungrouped.push(bank);
        }

        const grouped = [...groups.values()].map((group) => {
            const totals = group.banks
                .map((bank) => balanceForBank(bank.id)?.total)
                .filter((total): total is string => total !== undefined);

            const combinedTotal =
                totals.length > 0
                    ? totals
                          .reduce((sum, total) => sum + Number.parseFloat(total), 0)
                          .toFixed(2)
                    : null;

            return {
                ...group,
                banks: [...group.banks].sort((left, right) => {
                    if (left.spaceRole === 'main') {
                        return -1;
                    }

                    if (right.spaceRole === 'main') {
                        return 1;
                    }

                    return (left.accountLabel ?? '').localeCompare(right.accountLabel ?? '');
                }),
                total: combinedTotal,
            };
        });

        return [
            ...grouped,
            ...ungrouped.map((bank) => ({
                key: bank.id,
                label: '',
                banks: [bank],
                total: balanceForBank(bank.id)?.total ?? null,
            })),
        ];
    }, [banks, bankBalances]);

    return (
        <>
            <Head title="Banks" />
            <div className="flex items-center justify-between gap-4">
                <Heading
                    variant="small"
                    title="Banks"
                    description="Add every bank account you use so you can see which funds live where. These are references only — Kinsenas does not move money. You still transfer in your real banking apps so balances stay in sync."
                />
                <Button onClick={() => setAddModalOpen(true)} data-tour="add-bank">
                    <Plus /> Add bank
                </Button>
            </div>

            <Alert variant="info" className="mt-6" data-tour="banks-intro">
                <Info />
                <AlertTitle>Start with your banks</AlertTitle>
                <AlertDescription>
                    List all the banks (and GoSave spaces) you use here first. When you pick a savings
                    plan, you&apos;ll assign each fund to one of these accounts. This is your map of
                    where money should go — you still make the transfers yourself.
                    {banks.length > 0 && teamSlug !== '' && (
                        <>
                            {' '}
                            Next:{' '}
                            <Link
                                href={`/${teamSlug}/savings/plan`}
                                className="font-medium text-foreground underline-offset-4 hover:underline"
                            >
                                Choose a savings plan
                            </Link>
                            .
                        </>
                    )}
                </AlertDescription>
            </Alert>

            <AddBankModal
                open={addModalOpen}
                onOpenChange={setAddModalOpen}
                institutions={institutions}
            />

            <ul className="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {banks.length === 0 ? (
                    <li className="col-span-full rounded-lg border border-dashed p-6 text-sm text-muted-foreground">
                        <p className="font-medium text-foreground">No banks added yet</p>
                        <p className="mt-2">
                            Start here before you pick a savings plan. Add all the banks (and GoSave
                            spaces) you use, then assign funds to those accounts on your plan.
                        </p>
                        <p className="mt-2">
                            Reminder: this is your map of where money <em>should</em> go — you still
                            make the transfers yourself.
                        </p>
                        <Button className="mt-4" onClick={() => setAddModalOpen(true)}>
                            <Plus /> Add your first bank
                        </Button>
                    </li>
                ) : (
                    bankGroups.map((group) => {
                        if (group.banks.length === 1 && !group.banks[0].bankAccountGroupId) {
                            const bank = group.banks[0];
                            const balance = balanceForBank(bank.id);

                            return (
                                <li key={group.key} className="h-full rounded-lg border p-4">
                                    <BankListItem bank={bank} balance={balance} />
                                </li>
                            );
                        }

                        return (
                            <li key={group.key} className="h-full rounded-lg border p-4">
                                <div className="flex items-start gap-3">
                                    <BankLogo logoUrl={group.banks[0]?.logoUrl} name={group.label} />
                                    <div className="min-w-0 flex-1">
                                        <p className="font-medium">{group.label}</p>
                                        {group.total !== null && (
                                            <p className="mt-1 text-sm font-medium">
                                                Combined balance: {formatMoney(group.total)}
                                            </p>
                                        )}
                                    </div>
                                </div>
                                <ul className="mt-4 space-y-3 border-l pl-4">
                                    {group.banks.map((bank) => (
                                        <li key={bank.id}>
                                            <BankListItem
                                                bank={bank}
                                                balance={balanceForBank(bank.id)}
                                                compact
                                            />
                                        </li>
                                    ))}
                                </ul>
                            </li>
                        );
                    })
                )}
            </ul>
        </>
    );
}

function BankListItem({
    bank,
    balance,
    compact = false,
}: {
    bank: Bank;
    balance?: BankBalance;
    compact?: boolean;
}) {
    return (
        <div>
            <div className={compact ? '' : 'flex items-start gap-3'}>
                {!compact && <BankLogo logoUrl={bank.logoUrl} name={bank.name} />}
                <div className="min-w-0 flex-1">
                    <p className={compact ? 'text-sm font-medium' : 'font-medium'}>
                        {formatBankOptionLabel(bank)}
                    </p>
                    {balance && (
                        <p className="mt-1 text-sm font-medium">Balance: {formatMoney(balance.total)}</p>
                    )}
                </div>
            </div>
            {balance && balance.byCategory.length > 0 && (
                <div className="mt-3 overflow-hidden rounded-lg border">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b bg-muted/50 text-left">
                                <th className="px-3 py-2 font-medium">Fund</th>
                                <th className="px-3 py-2 text-right font-medium">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            {balance.byCategory.map((row) => (
                                <tr key={row.categoryId} className="border-b last:border-b-0">
                                    <td className="px-3 py-2 text-muted-foreground">{row.categoryName}</td>
                                    <td className="px-3 py-2 text-right">{formatMoney(row.total)}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </div>
    );
}

BanksIndex.layout = (props: SharedData) => ({
    breadcrumbs: [{ title: 'Banks', href: `/${props.currentTeam?.slug}/savings/banks` }],
});
