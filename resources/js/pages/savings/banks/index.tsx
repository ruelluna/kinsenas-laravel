import { Form, Head, usePage } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Bank } from '@/types/savings';
import type { SharedData } from '@/types';

type Props = { banks: Bank[] };

export default function BanksIndex({ banks }: Props) {
    const { currentTeam } = usePage<SharedData>().props;
    const teamSlug = currentTeam?.slug ?? '';

    return (
        <>
            <Head title="Banks" />
            <Heading variant="small" title="Banks" description="Accounts you transfer savings from." />

            <Form action={`/${teamSlug}/savings/banks`} method="post" className="mt-6 grid max-w-md gap-4 rounded-lg border p-4">
                <div className="grid gap-2">
                    <Label htmlFor="name">Bank name</Label>
                    <Input id="name" name="name" required />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="account_label">Account label</Label>
                    <Input id="account_label" name="account_label" />
                </div>
                <Button type="submit">Add bank</Button>
            </Form>

            <ul className="mt-8 space-y-2">
                {banks.map((bank) => (
                    <li key={bank.id} className="rounded-lg border p-3 text-sm">
                        <span className="font-medium">{bank.name}</span>
                        {bank.accountLabel && <span className="text-muted-foreground"> — {bank.accountLabel}</span>}
                    </li>
                ))}
            </ul>
        </>
    );
}

BanksIndex.layout = (page: { props: SharedData }) => ({
    breadcrumbs: [{ title: 'Banks', href: `/${page.props.currentTeam?.slug}/savings/banks` }],
});
