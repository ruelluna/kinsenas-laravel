import { Head, usePage } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { useMemo, useState } from 'react';
import Heading from '@/components/heading';
import DeleteRecipientModal from '@/components/savings/delete-recipient-modal';
import RecipientFormModal from '@/components/savings/recipient-form-modal';
import { Button } from '@/components/ui/button';
import { useRegisterMobileNavAction } from '@/hooks/use-register-mobile-nav-action';
import type { SharedData } from '@/types';
import type { Recipient } from '@/types/savings';

type Props = {
    recipients: Recipient[];
    recipientTypes: Array<{ value: string; label: string }>;
};

export default function RecipientsIndex({ recipients, recipientTypes }: Props) {
    const { currentTeam } = usePage<SharedData>().props;
    const teamSlug = currentTeam?.slug ?? '';
    const [formModalOpen, setFormModalOpen] = useState(false);
    const [deleteModalOpen, setDeleteModalOpen] = useState(false);
    const [selectedRecipient, setSelectedRecipient] =
        useState<Recipient | null>(null);
    const [editingRecipient, setEditingRecipient] = useState<Recipient | null>(
        null,
    );

    function openAddModal() {
        setEditingRecipient(null);
        setFormModalOpen(true);
    }

    function openEditModal(recipient: Recipient) {
        setEditingRecipient(recipient);
        setFormModalOpen(true);
    }

    function openDeleteModal(recipient: Recipient) {
        setSelectedRecipient(recipient);
        setDeleteModalOpen(true);
    }

    const mobileNavAction = useMemo(
        () => ({
            label: 'Add recipient',
            ariaLabel: 'Add recipient',
            icon: Plus,
            onClick: openAddModal,
        }),
        [],
    );

    useRegisterMobileNavAction(mobileNavAction);

    return (
        <>
            <Head title="Recipients" />
            <div className="flex flex-wrap items-center justify-between gap-3">
                <Heading
                    variant="small"
                    title="Recipients"
                    description="People and organizations receiving payments."
                />
                <Button
                    className="hidden shrink-0 md:inline-flex"
                    onClick={openAddModal}
                >
                    <Plus /> Add recipient
                </Button>
            </div>

            <RecipientFormModal
                open={formModalOpen}
                onOpenChange={setFormModalOpen}
                recipientTypes={recipientTypes}
                recipient={editingRecipient}
            />

            <DeleteRecipientModal
                open={deleteModalOpen}
                onOpenChange={setDeleteModalOpen}
                recipient={selectedRecipient}
                teamSlug={teamSlug}
            />

            <ul className="mt-6 space-y-2 md:mt-8">
                {recipients.length === 0 ? (
                    <li className="rounded-lg border border-dashed px-4 py-6 text-center text-sm text-muted-foreground">
                        No recipients yet. Add someone you pay regularly.
                    </li>
                ) : (
                    recipients.map((recipient) => (
                        <li
                            key={recipient.id}
                            className="flex items-center justify-between gap-3 rounded-lg border p-3 text-sm"
                        >
                            <div className="min-w-0">
                                <span className="font-medium">
                                    {recipient.name}
                                </span>
                                <span className="text-muted-foreground">
                                    {' '}
                                    — {recipient.typeLabel}
                                </span>
                                {recipient.notes && (
                                    <p className="mt-1 truncate text-xs text-muted-foreground">
                                        {recipient.notes}
                                    </p>
                                )}
                            </div>
                            <div className="flex shrink-0 gap-1">
                                <Button
                                    type="button"
                                    size="icon"
                                    variant="ghost"
                                    className="size-8"
                                    aria-label={`Edit ${recipient.name}`}
                                    onClick={() => openEditModal(recipient)}
                                >
                                    <Pencil className="size-4" />
                                </Button>
                                <Button
                                    type="button"
                                    size="icon"
                                    variant="ghost"
                                    className="size-8 text-destructive hover:text-destructive"
                                    aria-label={`Remove ${recipient.name}`}
                                    onClick={() => openDeleteModal(recipient)}
                                >
                                    <Trash2 className="size-4" />
                                </Button>
                            </div>
                        </li>
                    ))
                )}
            </ul>
        </>
    );
}

RecipientsIndex.layout = (props: SharedData) => ({
    breadcrumbs: [
        {
            title: 'Recipients',
            href: `/${props.currentTeam?.slug}/savings/recipients`,
        },
    ],
});
