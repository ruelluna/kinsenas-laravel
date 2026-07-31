import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { BankInstitutionSavingsSpaces } from '@/types/savings';

type SpaceRow = {
    label: string;
    enabled: boolean;
};

type Props = {
    config: BankInstitutionSavingsSpaces;
    mainLabel: string;
    onMainLabelChange: (value: string) => void;
    spaces: SpaceRow[];
    onSpacesChange: (spaces: SpaceRow[]) => void;
};

export default function GoSaveSpaceSetup({
    config,
    mainLabel,
    onMainLabelChange,
    spaces,
    onSpacesChange,
}: Props) {
    const updateSpace = (index: number, patch: Partial<SpaceRow>) => {
        onSpacesChange(
            spaces.map((space, spaceIndex) => (spaceIndex === index ? { ...space, ...patch } : space)),
        );
    };

    return (
        <div className="grid gap-4 rounded-md border bg-muted/20 p-4">
            <div>
                <p className="text-sm font-medium">GoSave spaces</p>
                <p className="text-xs text-muted-foreground">
                    GoTyme includes one main account and up to {config.max} GoSave spaces. Match the names you use in
                    the GoTyme app.
                </p>
            </div>

            <div className="grid gap-2">
                <Label htmlFor="main_label">Main account</Label>
                <Input
                    id="main_label"
                    name="main_label"
                    value={mainLabel}
                    onChange={(event) => onMainLabelChange(event.target.value)}
                />
            </div>

            <div className="grid gap-3">
                <Label>GoSave spaces</Label>
                {spaces.map((space, index) => (
                    <div key={index} className="flex items-center gap-3">
                        <Checkbox
                            checked={space.enabled}
                            onCheckedChange={(value) => updateSpace(index, { enabled: value === true })}
                        />
                        <Input
                            name={`spaces[${index}][label]`}
                            value={space.label}
                            onChange={(event) => updateSpace(index, { label: event.target.value })}
                            disabled={!space.enabled}
                            placeholder={`${config.spaceLabelPrefix} ${index + 1}`}
                        />
                        <input type="hidden" name={`spaces[${index}][enabled]`} value={space.enabled ? '1' : '0'} />
                    </div>
                ))}
            </div>
        </div>
    );
}

export function createDefaultGoSaveSpaces(config: BankInstitutionSavingsSpaces): SpaceRow[] {
    return Array.from({ length: config.max }, (_, index) => ({
        label: `${config.spaceLabelPrefix} ${index + 1}`,
        enabled: false,
    }));
}
