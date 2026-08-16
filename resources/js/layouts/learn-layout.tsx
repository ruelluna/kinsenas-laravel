import { usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import AppLayout from '@/layouts/app-layout';
import type { SharedData } from '@/types';

type Props = {
    children: ReactNode;
};

export default function LearnPageLayout({ children }: Props) {
    const { auth } = usePage<SharedData>().props;

    if (auth.user) {
        return <AppLayout>{children}</AppLayout>;
    }

    return <>{children}</>;
}
