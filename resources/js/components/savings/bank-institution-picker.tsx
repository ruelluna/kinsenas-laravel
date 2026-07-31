import { useMemo, useState } from 'react';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { BankInstitution } from '@/types/savings';

type Props = {
    institutions: BankInstitution[];
    name?: string;
    institutionId?: string;
    onChange: (selection: { institutionId: string; name: string } | null) => void;
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
    const [selectedId, setSelectedId] = useState(controlledId ?? '');

    const filtered = useMemo(() => {
        const normalized = query.trim().toLowerCase();

        if (normalized === '') {
            return institutions;
        }

        return institutions.filter((institution) =>
            institution.name.toLowerCase().includes(normalized),
        );
    }, [institutions, query]);

    const selected = institutions.find((institution) => institution.id === selectedId) ?? null;

    const handleSelect = (institution: BankInstitution) => {
        setSelectedId(institution.id);
        setQuery(institution.name);
        onChange({ institutionId: institution.id, name: institution.name });
    };

    const handleClear = () => {
        setSelectedId('');
        setQuery('');
        onChange(null);
    };

    return (
        <div className="grid gap-2">
            <Label htmlFor="bank-institution-search">Bank or e-wallet</Label>
            <Input
                id="bank-institution-search"
                value={query}
                onChange={(event) => {
                    setQuery(event.target.value);
                    if (selected && event.target.value !== selected.name) {
                        setSelectedId('');
                        onChange(null);
                    }
                }}
                placeholder="Search BDO, GCash, Maya…"
                autoComplete="off"
            />
            {selected && (
                <>
                    <input type="hidden" name={name} value={selected.id} />
                    <input type="hidden" name="name" value={selected.name} />
                </>
            )}
            {!selected && query !== '' && (
                <ul className="max-h-48 overflow-y-auto rounded-md border text-sm">
                    {filtered.length === 0 ? (
                        <li className="px-3 py-2 text-muted-foreground">No matches.</li>
                    ) : (
                        filtered.slice(0, 12).map((institution) => (
                            <li key={institution.id}>
                                <button
                                    type="button"
                                    className="flex w-full items-center gap-3 px-3 py-2 text-left hover:bg-muted/50"
                                    onClick={() => handleSelect(institution)}
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
                        ))
                    )}
                </ul>
            )}
            {selected && (
                <div className="flex items-center gap-3 rounded-md border p-3 text-sm">
                    {selected.logoUrl ? (
                        <img src={selected.logoUrl} alt="" className="size-8 object-contain" />
                    ) : null}
                    <div className="flex-1">
                        <p className="font-medium">{selected.name}</p>
                        <p className="text-xs text-muted-foreground capitalize">{selected.type.replace('_', ' ')}</p>
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
