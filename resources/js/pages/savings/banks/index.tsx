import { Form, Head, usePage } from '@inertiajs/react';
import { useState } from 'react';
import BankInstitutionPicker from '@/components/savings/bank-institution-picker';
import { BankLogo } from '@/components/savings/bank-select';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatMoney } from '@/lib/format-money';
import type { Bank, BankBalance, BankInstitution } from '@/types/savings';
import type { SharedData } from '@/types';

type Props = {
    banks: Bank[];
    institutions: BankInstitution[];
    bankBalances: BankBalance[];
};

export default function BanksIndex({ banks, institutions, bankBalances }: Props) {
    const { currentTeam } = usePage<SharedData>().props;
    const teamSlug = currentTeam?.slug ?? '';
    const [institutionSelection, setInstitutionSelection] = useState<{
        institutionId: string;
        name: string;
    } | null>(null);

    const balanceForBank = (bankId: string) => bankBalances.find((balance) => balance.bankId === bankId);

    return (
        <>
            <Head title="Banks" />
            <Heading
                variant="small"
                title="Banks"
                description="Accounts where you transfer savings. Balances reflect confirmed transfers minus spending."
            />

            <Form action={`/${teamSlug}/savings/banks`} method="post" className="mt-6 max-w-md space-y-4 rounded-lg border p-4">
                <BankInstitutionPicker
                    institutions={institutions}
                    onChange={setInstitutionSelection}
                />
                <div className="grid gap-2">
                    <Label htmlFor="account_label">Account label (optional)</Label>
                    <Input id="account_label" name="account_label" placeholder="Savings, Payroll, Main…" />
                </div>
                <Button type="submit" disabled={institutionSelection === null}>
                    Add bank
                </Button>
            </Form>

            <ul className="mt-8 space-y-4">
                {banks.length === 0 ? (
                    <li className="text-sm text-muted-foreground">No banks added yet.</li>
                ) : (
                    banks.map((bank) => {
                        const balance = balanceForBank(bank.id);

                        return (
                            <li key={bank.id} className="rounded-lg border p-4">
                                <div className="flex items-start gap-3">
                                    <BankLogo logoUrl={bank.logoUrl} name={bank.name} />
                                    <div className="min-w-0 flex-1">
                                        <p className="font-medium">{bank.name}</p>
                                        {bank.accountLabel && (
                                            <p className="text-sm text-muted-foreground">{bank.accountLabel}</p>
                                        )}
                                        {balance && (
                                            <p className="mt-2 text-sm font-medium">
                                                Balance: {formatMoney(balance.total)}
                                            </p>
                                        )}
                                    </div>
                                </div>
                                {balance && balance.byCategory.length > 0 && (
                                    <Collapsible className="mt-3">
                                        <CollapsibleTrigger asChild>
                                            <Button type="button" variant="ghost" size="sm" className="px-0">
                                                By fund
                                            </Button>
                                        </CollapsibleTrigger>
                                        <CollapsibleContent>
                                            <ul className="mt-2 space-y-1 text-sm">
                                                {balance.byCategory.map((row) => (
                                                    <li key={row.categoryId} className="flex justify-between gap-2">
                                                        <span className="text-muted-foreground">{row.categoryName}</span>
                                                        <span>{formatMoney(row.total)}</span>
                                                    </li>
                                                ))}
                                            </ul>
                                        </CollapsibleContent>
                                    </Collapsible>
                                )}
                            </li>
                        );
                    })
                )}
            </ul>
        </>
    );
}

BanksIndex.layout = (props: SharedData) => ({
    breadcrumbs: [{ title: 'Banks', href: `/${props.currentTeam?.slug}/savings/banks` }],
});
