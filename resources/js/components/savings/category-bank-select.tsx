import BankOptionSelect from '@/components/savings/bank-option-select';
import { Label } from '@/components/ui/label';
import type { BankOption } from '@/types/savings';

type Props = {
    banks: BankOption[];
    selectedId: string;
    onChange: (bankId: string) => void;
    namePrefix: string;
};

export default function CategoryBankSelect({
    banks,
    selectedId,
    onChange,
    namePrefix,
}: Props) {
    if (banks.length === 0) {
        return (
            <p className="text-xs text-muted-foreground">
                Add banks under Banks in the sidebar first, then assign an
                account to this fund bucket.
            </p>
        );
    }

    return (
        <div className="grid gap-2">
            <Label htmlFor={`${namePrefix}-bank`}>Assigned bank</Label>
            <BankOptionSelect
                id={`${namePrefix}-bank`}
                name={`${namePrefix}[bank_id]`}
                banks={banks}
                value={selectedId}
                onChange={onChange}
                allowEmpty
                emptyLabel="None"
                placeholder="Select a bank"
            />
            <p className="text-xs text-muted-foreground">
                One bank or GoSave space per fund bucket. The same account can
                be used for multiple fund buckets.
            </p>
        </div>
    );
}
