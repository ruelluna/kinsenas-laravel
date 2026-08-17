import { Form, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';

type Size = 'default' | 'sm' | 'lg' | 'icon';

type AdminEditLinkProps = {
    href: string;
    label?: string;
    size?: Size;
};

type AdminInfoLinkProps = {
    href: string;
    label?: string;
    size?: Size;
};

type AdminDeleteFormProps = {
    action: string;
    label?: string;
    size?: Size;
    testId?: string;
};

export function AdminEditLink({ href, label = 'Edit', size = 'sm' }: AdminEditLinkProps) {
    return (
        <Button variant="warning" size={size} asChild>
            <Link href={href}>{label}</Link>
        </Button>
    );
}

export function AdminInfoLink({ href, label = 'Preview', size = 'sm' }: AdminInfoLinkProps) {
    return (
        <Button variant="info" size={size} asChild>
            <Link href={href}>{label}</Link>
        </Button>
    );
}

export function AdminDeleteForm({
    action,
    label = 'Delete',
    size = 'sm',
    testId,
}: AdminDeleteFormProps) {
    return (
        <Form action={action} method="delete">
            <Button type="submit" variant="destructive" size={size} data-test={testId}>
                {label}
            </Button>
        </Form>
    );
}

type AdminRowActionsProps = {
    previewHref?: string;
    previewLabel?: string;
    editHref?: string;
    editLabel?: string;
    deleteAction?: string;
    deleteLabel?: string;
    deleteTestId?: string;
};

export function AdminRowActions({
    previewHref,
    previewLabel,
    editHref,
    editLabel,
    deleteAction,
    deleteLabel,
    deleteTestId,
}: AdminRowActionsProps) {
    return (
        <div className="flex flex-wrap gap-2">
            {previewHref && <AdminInfoLink href={previewHref} label={previewLabel} />}
            {editHref && <AdminEditLink href={editHref} label={editLabel} />}
            {deleteAction && (
                <AdminDeleteForm
                    action={deleteAction}
                    label={deleteLabel}
                    testId={deleteTestId}
                />
            )}
        </div>
    );
}
