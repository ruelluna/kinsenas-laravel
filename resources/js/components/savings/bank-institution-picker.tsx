import { useMemo, useState } from 'react';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { BankInstitution } from '@/types/savings';

export type BankInstitutionSelection =
    | { mode: 'institution'; institutionId: string; name: string }
    | { mode: 'custom'; name: string }
    | null;

type Props = {
    institutions: BankInstitution[];
    name?: string;
    institutionId?: string;
    onChange: (selection: BankInstitutionSelection) => void;
    error?: string;
};

export default function BankInstitutionPicker({
    institutions,
    name = 'bank_institution_id',
    institutionId: controlledId,
    onChange,
    error,
}: Props) {
    const [query, setQuery] = useState('');
    const [selection, setSelection] = useState<BankInstitutionSelection>(
        controlledId
            ? {
                  mode: 'institution',
                  institutionId: controlledId,
                  name:
                      institutions.find(
                          (institution) => institution.id === controlledId,
                      )?.name ?? '',
              }
            : null,
    );
    const [customName, setCustomName] = useState('');

    const filtered = useMemo(() => {
        const normalized = query.trim().toLowerCase();

        if (normalized === '') {
            return institutions;
        }

        return institutions.filter((institution) =>
            institution.name.toLowerCase().includes(normalized),
        );
    }, [institutions, query]);

    const trimmedQuery = query.trim();

    const selectedInstitution =
        selection?.mode === 'institution'
            ? (institutions.find(
                  (institution) =>
                      institution.id === selection.institutionId,
              ) ?? null)
            : null;

    const handleSelectInstitution = (institution: BankInstitution) => {
        const nextSelection: BankInstitutionSelection = {
            mode: 'institution',
            institutionId: institution.id,
            name: institution.name,
        };

        setSelection(nextSelection);
        setQuery(institution.name);
        setCustomName('');
        onChange(nextSelection);
    };

    const handleSelectCustom = (initialName: string) => {
        const nextSelection: BankInstitutionSelection = {
            mode: 'custom',
            name: initialName,
        };

        setSelection(nextSelection);
        setCustomName(initialName);
        setQuery('');
        onChange(nextSelection);
    };

    const handleClear = () => {
        setSelection(null);
        setQuery('');
        setCustomName('');
        onChange(null);
    };

    const handleCustomNameChange = (value: string) => {
        setCustomName(value);
        onChange({ mode: 'custom', name: value });
    };

    if (selection?.mode === 'custom') {
        return (
            <div className="grid gap-2">
                <Label htmlFor="custom-bank-name">Bank name</Label>
                <Input
                    id="custom-bank-name"
                    name="name"
                    value={customName}
                    onChange={(event) =>
                        handleCustomNameChange(event.target.value)
                    }
                    placeholder="Enter bank or e-wallet name"
                    required
                    autoComplete="off"
                />
                <div className="flex items-center gap-3 rounded-md border p-3 text-sm">
                    <span className="flex size-8 shrink-0 items-center justify-center rounded bg-muted text-sm font-medium">
                        {customName.trim().charAt(0).toUpperCase() || '?'}
                    </span>
                    <div className="flex-1">
                        <p className="font-medium">
                            {customName.trim() || 'Custom bank'}
                        </p>
                        <p className="text-xs text-muted-foreground">
                            Custom bank
                        </p>
                    </div>
                    <button
                        type="button"
                        className="text-xs text-muted-foreground underline-offset-4 hover:underline"
                        onClick={handleClear}
                    >
                        Change
                    </button>
                </div>
                {error && <p className="text-sm text-destructive">{error}</p>}
            </div>
        );
    }

    return (
        <div className="grid gap-2">
            <Label htmlFor="bank-institution-search">Bank or e-wallet</Label>
            <Input
                id="bank-institution-search"
                value={query}
                onChange={(event) => {
                    setQuery(event.target.value);

                    if (
                        selectedInstitution &&
                        event.target.value !== selectedInstitution.name
                    ) {
                        setSelection(null);
                        onChange(null);
                    }
                }}
                placeholder="Search BDO, GCash, Maya…"
                autoComplete="off"
            />
            {selectedInstitution && (
                <>
                    <input
                        type="hidden"
                        name={name}
                        value={selectedInstitution.id}
                    />
                    <input
                        type="hidden"
                        name="name"
                        value={selectedInstitution.name}
                    />
                </>
            )}
            {!selectedInstitution && query !== '' && (
                <div className="overflow-hidden rounded-md border text-sm">
                    {filtered.length === 0 && trimmedQuery !== '' ? (
                        <button
                            type="button"
                            className="flex w-full px-3 py-2 text-left hover:bg-muted/50"
                            onClick={() => handleSelectCustom(trimmedQuery)}
                        >
                            Use &ldquo;{trimmedQuery}&rdquo;
                        </button>
                    ) : (
                        <ul className="max-h-48 overflow-y-auto">
                            {filtered.slice(0, 12).map((institution) => (
                                <li key={institution.id}>
                                    <button
                                        type="button"
                                        className="flex w-full items-center gap-3 px-3 py-2 text-left hover:bg-muted/50"
                                        onClick={() =>
                                            handleSelectInstitution(institution)
                                        }
                                    >
                                        {institution.logoUrl ? (
                                            <img
                                                src={institution.logoUrl}
                                                alt=""
                                                className="size-6 shrink-0 object-contain"
                                            />
                                        ) : (
                                            <span className="flex size-6 shrink-0 items-center justify-center rounded bg-muted text-xs">
                                                {institution.name.charAt(0)}
                                            </span>
                                        )}
                                        <span>{institution.name}</span>
                                    </button>
                                </li>
                            ))}
                        </ul>
                    )}
                    <div className="border-t bg-muted/20">
                        <button
                            type="button"
                            className="flex w-full px-3 py-2 text-left font-medium hover:bg-muted/50"
                            onClick={() => handleSelectCustom(trimmedQuery)}
                        >
                            Other bank…
                        </button>
                    </div>
                </div>
            )}
            {selectedInstitution && (
                <div className="flex items-center gap-3 rounded-md border p-3 text-sm">
                    {selectedInstitution.logoUrl ? (
                        <img
                            src={selectedInstitution.logoUrl}
                            alt=""
                            className="size-8 object-contain"
                        />
                    ) : null}
                    <div className="flex-1">
                        <p className="font-medium">
                            {selectedInstitution.name}
                        </p>
                        <p className="text-xs text-muted-foreground capitalize">
                            {selectedInstitution.type.replace('_', ' ')}
                        </p>
                    </div>
                    <button
                        type="button"
                        className="text-xs text-muted-foreground underline-offset-4 hover:underline"
                        onClick={handleClear}
                    >
                        Change
                    </button>
                </div>
            )}
            {error && <p className="text-sm text-destructive">{error}</p>}
        </div>
    );
}
