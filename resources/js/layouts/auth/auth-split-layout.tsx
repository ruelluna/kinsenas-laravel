import { Link } from '@inertiajs/react';
import { KINSENAS_HORIZONTAL_LOGO } from '@/lib/brand';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

export default function AuthSplitLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div className="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            <div className="relative hidden h-full flex-col bg-primary p-10 text-primary-foreground lg:flex dark:border-r">
                <Link href={home()} className="relative z-20 flex items-center">
                    <img
                        src={KINSENAS_HORIZONTAL_LOGO}
                        alt="Kinsenas"
                        className="h-10 w-auto max-w-full object-contain"
                    />
                </Link>
            </div>
            <div className="w-full lg:p-8">
                <div className="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    <Link
                        href={home()}
                        className="relative z-20 flex items-center justify-center lg:hidden"
                    >
                        <img
                            src={KINSENAS_HORIZONTAL_LOGO}
                            alt="Kinsenas"
                            className="h-10 w-auto max-w-full object-contain sm:h-12"
                        />
                    </Link>
                    <div className="flex flex-col items-start gap-2 text-left sm:items-center sm:text-center">
                        <h1 className="text-xl font-medium">{title}</h1>
                        <p className="text-sm text-balance text-muted-foreground">
                            {description}
                        </p>
                    </div>
                    {children}
                </div>
            </div>
        </div>
    );
}
