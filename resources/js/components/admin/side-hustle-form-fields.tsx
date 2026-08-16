import TiptapEditor from '@/components/admin/tiptap-editor';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { selectClassName, textareaClassName } from '@/lib/form-field-classes';
import type { SideHustleAdmin } from '@/types/learn-library';

type Props = {
    hustle?: SideHustleAdmin;
    categoryOptions: Array<{ id: string; name: string }>;
};

export default function SideHustleFormFields({ hustle, categoryOptions }: Props) {
    return (
        <>
            <div className="grid gap-2">
                <Label htmlFor="side_hustle_category_id">Category</Label>
                <select
                    id="side_hustle_category_id"
                    name="side_hustle_category_id"
                    className={selectClassName}
                    defaultValue={hustle?.sideHustleCategoryId ?? ''}
                    required
                >
                    <option value="">Select category</option>
                    {categoryOptions.map((category) => (
                        <option key={category.id} value={category.id}>
                            {category.name}
                        </option>
                    ))}
                </select>
            </div>
            <div className="grid gap-2">
                <Label htmlFor="post_as">Post as (optional)</Label>
                <Input
                    id="post_as"
                    name="post_as"
                    placeholder="e.g. Kinsenas Editorial"
                    defaultValue={hustle?.postAs ?? ''}
                />
                <p className="text-xs text-muted-foreground">
                    Shown on the hustle page as &quot;By [Post as name]&quot; when set.
                </p>
            </div>
            <div className="grid gap-2">
                <Label htmlFor="title">Title</Label>
                <Input id="title" name="title" defaultValue={hustle?.title ?? ''} required />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="slug">Slug</Label>
                <Input id="slug" name="slug" defaultValue={hustle?.slug ?? ''} required />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="excerpt">Excerpt</Label>
                <textarea
                    id="excerpt"
                    name="excerpt"
                    className={textareaClassName}
                    defaultValue={hustle?.excerpt ?? ''}
                />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="body">Guide body</Label>
                <TiptapEditor name="body" defaultValue={hustle?.body ?? ''} required />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="cover_image_url">Cover image URL</Label>
                <Input
                    id="cover_image_url"
                    name="cover_image_url"
                    type="url"
                    defaultValue={hustle?.coverImageUrl ?? ''}
                />
            </div>
            <div className="grid gap-4 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="difficulty">Difficulty</Label>
                    <select
                        id="difficulty"
                        name="difficulty"
                        className={selectClassName}
                        defaultValue={hustle?.difficulty ?? 'beginner'}
                    >
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                    </select>
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="capital_tier">Capital tier</Label>
                    <select
                        id="capital_tier"
                        name="capital_tier"
                        className={selectClassName}
                        defaultValue={hustle?.capitalTier ?? 'low'}
                    >
                        <option value="low">Low capital</option>
                        <option value="moderate">Moderate capital</option>
                        <option value="high">High capital</option>
                    </select>
                </div>
            </div>
            <div className="grid gap-4 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="startup_capital_min">Startup capital min (PHP)</Label>
                    <Input
                        id="startup_capital_min"
                        name="startup_capital_min"
                        type="number"
                        min={0}
                        defaultValue={hustle?.startupCapitalMin ?? ''}
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="startup_capital_max">Startup capital max (PHP)</Label>
                    <Input
                        id="startup_capital_max"
                        name="startup_capital_max"
                        type="number"
                        min={0}
                        defaultValue={hustle?.startupCapitalMax ?? ''}
                    />
                </div>
            </div>
            <div className="grid gap-4 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="time_commitment_hours_min">Hours per week (min)</Label>
                    <Input
                        id="time_commitment_hours_min"
                        name="time_commitment_hours_min"
                        type="number"
                        min={0}
                        max={168}
                        defaultValue={hustle?.timeCommitmentHoursMin ?? ''}
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="time_commitment_hours_max">Hours per week (max)</Label>
                    <Input
                        id="time_commitment_hours_max"
                        name="time_commitment_hours_max"
                        type="number"
                        min={0}
                        max={168}
                        defaultValue={hustle?.timeCommitmentHoursMax ?? ''}
                    />
                </div>
            </div>
            <div className="grid gap-2">
                <Label htmlFor="skills">Skills (comma-separated)</Label>
                <Input
                    id="skills"
                    name="skills"
                    defaultValue={hustle?.skills?.join(', ') ?? ''}
                />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="equipment">Equipment (comma-separated)</Label>
                <Input
                    id="equipment"
                    name="equipment"
                    defaultValue={hustle?.equipment?.join(', ') ?? ''}
                />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="publish_scope">Publish scope</Label>
                <select
                    id="publish_scope"
                    name="publish_scope"
                    className={selectClassName}
                    defaultValue={hustle?.publishScope ?? 'internal'}
                >
                    <option value="internal">Internal only</option>
                    <option value="external">External only</option>
                    <option value="both">Internal & external</option>
                </select>
            </div>
            <div className="grid gap-2">
                <Label htmlFor="status">Status</Label>
                <select
                    id="status"
                    name="status"
                    className={selectClassName}
                    defaultValue={hustle?.status ?? 'draft'}
                >
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
            <div className="grid gap-2">
                <Label htmlFor="sort_order">Sort order</Label>
                <Input
                    id="sort_order"
                    name="sort_order"
                    type="number"
                    defaultValue={hustle?.sortOrder ?? 0}
                />
            </div>
        </>
    );
}
