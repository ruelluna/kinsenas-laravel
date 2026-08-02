import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { AdminPlan, SubscriptionFeatureOption } from '@/types/billing';

type Props = {
    plan?: AdminPlan;
    features: SubscriptionFeatureOption[];
};

const textareaClassName =
    'border-input min-h-20 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none';

export function PlanFormFields({ plan, features }: Props) {
    const selectedFeatures =
        plan?.features ?? features.map((feature) => feature.value);

    return (
        <>
            <div className="grid gap-2">
                <Label htmlFor="name">Name</Label>
                <Input
                    id="name"
                    name="name"
                    defaultValue={plan?.name ?? ''}
                    required
                />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="slug">Slug</Label>
                <Input
                    id="slug"
                    name="slug"
                    defaultValue={plan?.slug ?? ''}
                    required
                />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="trial_days">Trial days</Label>
                <Input
                    id="trial_days"
                    name="trial_days"
                    type="number"
                    min={0}
                    max={365}
                    defaultValue={plan?.trialDays ?? 14}
                    required
                />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="sort_order">Sort order</Label>
                <Input
                    id="sort_order"
                    name="sort_order"
                    type="number"
                    min={0}
                    defaultValue={plan?.sortOrder ?? 1}
                    required
                />
            </div>
            <label className="flex items-center gap-2 text-sm">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    defaultChecked={plan?.isActive ?? true}
                />
                Active
            </label>
            <fieldset className="space-y-2">
                <legend className="text-sm font-medium">Features</legend>
                {features.map((feature) => (
                    <label
                        key={feature.value}
                        className="flex items-center gap-2 text-sm"
                    >
                        <input
                            type="checkbox"
                            name="features[]"
                            value={feature.value}
                            defaultChecked={selectedFeatures.includes(
                                feature.value,
                            )}
                        />
                        {feature.label}
                    </label>
                ))}
            </fieldset>
            <div className="grid gap-4 sm:grid-cols-2">
                <div className="space-y-3 rounded-lg border p-4">
                    <p className="text-sm font-medium">
                        Monthly price (centavos)
                    </p>
                    <Input
                        name="prices[monthly][amount]"
                        type="number"
                        min={0}
                        defaultValue={plan?.monthlyAmount ?? 29900}
                        required
                    />
                    <label className="flex items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            name="prices[monthly][is_active]"
                            value="1"
                            defaultChecked={plan?.monthlyActive ?? true}
                        />
                        Active
                    </label>
                </div>
                <div className="space-y-3 rounded-lg border p-4">
                    <p className="text-sm font-medium">
                        Yearly price (centavos)
                    </p>
                    <Input
                        name="prices[yearly][amount]"
                        type="number"
                        min={0}
                        defaultValue={plan?.yearlyAmount ?? 299000}
                        required
                    />
                    <label className="flex items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            name="prices[yearly][is_active]"
                            value="1"
                            defaultChecked={plan?.yearlyActive ?? true}
                        />
                        Active
                    </label>
                </div>
            </div>
            <p className="text-xs text-muted-foreground">
                Enter amounts in centavos (e.g. 29900 = ₱299.00). Display uses
                formatMoneyFromCents on list pages.
            </p>
        </>
    );
}

export { textareaClassName };
