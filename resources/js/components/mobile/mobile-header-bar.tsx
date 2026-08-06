import { Link } from '@inertiajs/react';
import { ChevronLeft } from 'lucide-react';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs: BreadcrumbItem[];
};

export function MobileHeaderBar({ breadcrumbs }: Props) {
    const current = breadcrumbs.at(-1);
    const parent = breadcrumbs.length > 1 ? breadcrumbs.at(-2) : null;
    const title = current?.title ?? 'Kinsenas';

    return (
        <>
            {parent ? (
                <Button
                    variant="ghost"
                    size="icon"
                    className="size-9 shrink-0"
                    asChild
                >
                    <Link
                        href={parent.href}
                        prefetch
                        aria-label={`Back to ${parent.title}`}
                    >
                        <ChevronLeft className="size-5" />
                    </Link>
                </Button>
            ) : null}
            <h1 className="min-w-0 flex-1 truncate font-space text-base font-bold leading-tight text-foreground">
                {title}
            </h1>
        </>
    );
}
