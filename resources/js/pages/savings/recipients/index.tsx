import { Head } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useMemo, useState } from 'react';
import Heading from '@/components/heading';
import AddRecipientModal from '@/components/savings/add-recipient-modal';
import { Button } from '@/components/ui/button';
import { useRegisterMobileNavAction } from '@/hooks/use-register-mobile-nav-action';
import type { SharedData } from '@/types';
import type { Recipient } from '@/types/savings';

type Props = {
    recipients: Recipient[];
    recipientTypes: Array<{ value: string; label: string }>;
};

export default function RecipientsIndex({ recipients, recipientTypes }: Props) {
    const [addModalOpen, setAddModalOpen] = useState(false);

    const mobileNavAction = useMemo(
        () => ({
            label: 'Add recipient',
            ariaLabel: 'Add recipient',
            icon: Plus,
            onClick: () => setAddModalOpen(true),
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
                    onClick={() => setAddModalOpen(true)}
                >
                    <Plus /> Add recipient
                </Button>
            </div>

            <AddRecipientModal
                open={addModalOpen}
                onOpenChange={setAddModalOpen}
                recipientTypes={recipientTypes}
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
                            className="rounded-lg border p-3 text-sm"
                        >
                            <span className="font-medium">{recipient.name}</span>
                            <span className="text-muted-foreground">
                                {' '}
                                — {recipient.typeLabel}
                            </span>
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
