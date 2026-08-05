import { useEffect, useState } from 'react';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type AccountType = 'GoTyme' | 'GoSave';

type Props = {
    onLabelChange: (label: string) => void;
};

function composeAccountLabel(type: AccountType, name: string): string {
    const trimmedName = name.trim();

    if (trimmedName === '') {
        return type;
    }

    return `${type}/${trimmedName}`;
}

export default function GoTymeAccountLabel({ onLabelChange }: Props) {
    const [accountType, setAccountType] = useState<AccountType>('GoTyme');
    const [name, setName] = useState('Main');

    useEffect(() => {
        onLabelChange(composeAccountLabel(accountType, name));
    }, [accountType, name, onLabelChange]);

    const accountLabel = composeAccountLabel(accountType, name);

    return (
        <div className="grid gap-4 rounded-md border bg-muted/20 p-4">
            <div>
                <p className="text-sm font-medium">Account type</p>
                <p className="text-xs text-muted-foreground">
                    Add each GoTyme main account and every GoSave (yours,
                    family, partner) separately.
                </p>
            </div>

            <fieldset className="grid gap-2">
                <legend className="text-sm font-medium">GoTyme or GoSave</legend>
                <div className="flex flex-wrap gap-4">
                    {(['GoTyme', 'GoSave'] as const).map((type) => (
                        <label
                            key={type}
                            className="flex cursor-pointer items-center gap-2 text-sm"
                        >
                            <input
                                type="radio"
                                name="gotyme_account_type"
                                value={type}
                                checked={accountType === type}
                                data-test={`gotyme-account-type-${type.toLowerCase()}`}
                                onChange={() => {
                                    setAccountType(type);
                                    setName(type === 'GoTyme' ? 'Main' : '');
                                }}
                                className="size-4 accent-primary"
                            />
                            {type}
                        </label>
                    ))}
                </div>
            </fieldset>

            <div className="grid gap-2">
                <Label htmlFor="gotyme_account_name">
                    {accountType === 'GoTyme' ? 'Account name' : 'GoSave name'}
                </Label>
                <Input
                    id="gotyme_account_name"
                    data-test="gotyme-account-name"
                    value={name}
                    onChange={(event) => setName(event.target.value)}
                    placeholder={
                        accountType === 'GoTyme'
                            ? 'Main'
                            : 'Mom, Partner, Vacation…'
                    }
                    autoComplete="off"
                />
            </div>

            <input type="hidden" name="account_label" value={accountLabel} />
        </div>
    );
}
