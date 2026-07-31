import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import type { BankOption } from '@/types/savings';

type Props = {
    banks: BankOption[];
    selectedIds: string[];
    onChange: (bankIds: string[]) => void;
    namePrefix: string;
};

export default function CategoryBankSelect({ banks, selectedIds, onChange, namePrefix }: Props) {
    if (banks.length === 0) {
        return (
            <p className="text-xs text-muted-foreground">
                Add banks under Banks in the sidebar to assign accounts to this fund.
            </p>
        );
    }

    const toggleBank = (bankId: string, checked: boolean) => {
        if (checked) {
            onChange([...selectedIds, bankId]);
            return;
        }

        onChange(selectedIds.filter((id) => id !== bankId));
    };

    return (
        <div className="grid gap-2">
            <Label>Assigned banks</Label>
            {selectedIds.map((id, index) => (
                <input key={id} type="hidden" name={`${namePrefix}[bank_ids][${index}]`} value={id} />
            ))}
            <div className="flex flex-wrap gap-3">
                {banks.map((bank) => {
                    const checked = selectedIds.includes(bank.id);

                    return (
                        <label
                            key={bank.id}
                            className="flex cursor-pointer items-center gap-2 rounded-md border px-3 py-2 text-sm"
                        >
                            <Checkbox
                                checked={checked}
                                onCheckedChange={(value) => toggleBank(bank.id, value === true)}
                            />
                            {bank.logoUrl ? (
                                <img src={bank.logoUrl} alt="" className="size-5 object-contain" />
                            ) : null}
                            <span>{bank.name}</span>
                        </label>
                    );
                })}
            </div>
        </div>
    );
}
