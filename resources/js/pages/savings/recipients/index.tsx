import { Head } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useState } from 'react';
import Heading from '@/components/heading';
import AddRecipientModal from '@/components/savings/add-recipient-modal';
import { Button } from '@/components/ui/button';
import type { SharedData } from '@/types';
import type { Recipient } from '@/types/savings';

type Props = {
    recipients: Recipient[];
    recipientTypes: Array<{ value: string; label: string }>;
};

export default function RecipientsIndex({ recipients, recipientTypes }: Props) {
    const [addModalOpen, setAddModalOpen] = useState(false);

    return (
        <>
            <Head title="Recipients" />
            <div className="flex items-center justify-between">
                <Heading
                    variant="small"
                    title="Recipients"
                    description="People and organizations receiving payments."
                />
                <Button onClick={() => setAddModalOpen(true)}>
                    <Plus /> Add recipient
                </Button>
            </div>

            <AddRecipientModal
                open={addModalOpen}
                onOpenChange={setAddModalOpen}
                recipientTypes={recipientTypes}
            />

            <ul className="mt-8 space-y-2">
                {recipients.map((recipient) => (
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
                ))}
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
