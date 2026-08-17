import { Label } from '@/components/ui/label';

type CategoryOption = {
    id: string;
    name: string;
};

type Props = {
    categories: CategoryOption[];
    selectedIds?: string[];
    inputName?: string;
};

export default function CategoryCheckboxGroup({
    categories,
    selectedIds = [],
    inputName = 'category_ids',
}: Props) {
    return (
        <div className="grid gap-2">
            <Label>Categories</Label>
            <div className="flex flex-wrap gap-3">
                {categories.map((category) => (
                    <label
                        key={category.id}
                        className="flex items-center gap-2 text-sm"
                    >
                        <input
                            type="checkbox"
                            name={`${inputName}[]`}
                            value={category.id}
                            defaultChecked={selectedIds.includes(category.id)}
                            className="size-4 rounded border border-input"
                        />
                        {category.name}
                    </label>
                ))}
            </div>
            <p className="text-xs text-muted-foreground">Select one or more categories.</p>
        </div>
    );
}
