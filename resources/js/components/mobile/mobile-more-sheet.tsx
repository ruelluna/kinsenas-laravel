import { Link, usePage } from '@inertiajs/react';
import { ChevronRight, LogOut, Settings } from 'lucide-react';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { useInitials } from '@/hooks/use-initials';
import { InstallAppMenuItem } from '@/components/pwa/install-app-menu-item';
import { adminNavItems } from '@/lib/admin-nav';
import { buildMemberNav } from '@/lib/member-nav';
import { cn } from '@/lib/utils';
import { logout } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import type { SharedData, User } from '@/types';

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export function MobileMoreSheet({ open, onOpenChange }: Props) {
    const page = usePage<SharedData>();
    const { auth, currentTeam } = page.props;
    const { isCurrentOrParentUrl } = useCurrentUrl();
    const { moreItems, hasAccess } = buildMemberNav(page.props);
    const isPlatformAdmin = Boolean(auth.user?.isPlatformAdmin);

    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            <SheetContent
                side="bottom"
                className="max-h-[85dvh] overflow-y-auto rounded-t-2xl pb-[calc(1rem+env(safe-area-inset-bottom))]"
            >
                <SheetHeader className="text-left">
                    <SheetTitle>More</SheetTitle>
                    <SheetDescription>
                        Banks, plan, transfers, and account settings
                    </SheetDescription>
                </SheetHeader>

                <nav className="mt-4 flex flex-col gap-1" aria-label="More">
                    {moreItems.map((item) => (
                        <Button
                            key={item.title}
                            variant="ghost"
                            className={cn('h-11 justify-start gap-3 px-3', {
                                'bg-muted': isCurrentOrParentUrl(item.href),
                            })}
                            asChild
                            onClick={() => onOpenChange(false)}
                        >
                            <Link href={item.href} prefetch>
                                {item.icon && (
                                    <item.icon className="size-5 shrink-0" />
                                )}
                                {item.title}
                            </Link>
                        </Button>
                    ))}

                    <InstallAppMenuItem onActivate={() => onOpenChange(false)} />

                    <Button
                        variant="ghost"
                        className={cn('h-11 justify-start gap-3 px-3', {
                            'bg-muted': isCurrentOrParentUrl(editProfile()),
                        })}
                        asChild
                        onClick={() => onOpenChange(false)}
                    >
                        <Link href={editProfile()} prefetch>
                            <Settings className="size-5 shrink-0" />
                            Settings
                        </Link>
                    </Button>
                </nav>

                {hasAccess && isPlatformAdmin && (
                    <div className="mt-4 border-t pt-4">
                        <p className="mb-2 px-3 text-xs font-medium tracking-wide text-muted-foreground uppercase">
                            Admin
                        </p>
                        <div className="flex flex-col gap-1">
                            {adminNavItems.map((item) => (
                                <Button
                                    key={item.title}
                                    variant="ghost"
                                    className={cn(
                                        'h-11 justify-start gap-3 px-3',
                                        {
                                            'bg-muted': isCurrentOrParentUrl(
                                                item.href,
                                            ),
                                        },
                                    )}
                                    asChild
                                    onClick={() => onOpenChange(false)}
                                >
                                    <Link href={item.href} prefetch>
                                        {item.icon && (
                                            <item.icon className="size-5 shrink-0" />
                                        )}
                                        {item.title}
                                    </Link>
                                </Button>
                            ))}
                        </div>
                    </div>
                )}

                <MobileAccountSection
                    user={auth.user}
                    teamName={currentTeam?.name ?? null}
                    onNavigate={() => onOpenChange(false)}
                />
            </SheetContent>
        </Sheet>
    );
}

function MobileAccountSection({
    user,
    teamName,
    onNavigate,
}: {
    user: User;
    teamName: string | null;
    onNavigate: () => void;
}) {
    const getInitials = useInitials();
    const showAvatar = Boolean(user.avatar && user.avatar !== '');

    return (
        <div className="mt-4 border-t pt-4">
            <p className="mb-2 px-3 text-xs font-medium tracking-wide text-muted-foreground uppercase">
                Account
            </p>
            <div className="overflow-hidden rounded-xl border bg-card shadow-sm">
                <Link
                    href={editProfile()}
                    prefetch
                    onClick={onNavigate}
                    className="flex items-center gap-3 p-4 transition-colors hover:bg-muted/50 active:bg-muted"
                >
                    <Avatar className="size-12 shrink-0 rounded-xl">
                        {showAvatar ? (
                            <AvatarImage src={user.avatar} alt={user.name} />
                        ) : null}
                        <AvatarFallback className="rounded-xl bg-primary/10 text-sm font-semibold text-primary">
                            {getInitials(user.name)}
                        </AvatarFallback>
                    </Avatar>
                    <div className="min-w-0 flex-1 text-left">
                        <p className="truncate font-semibold leading-snug">
                            {user.name}
                        </p>
                        <p className="truncate text-sm text-muted-foreground">
                            {user.email}
                        </p>
                        {teamName ? (
                            <p className="mt-1 truncate text-xs font-medium text-muted-foreground">
                                {teamName}
                            </p>
                        ) : null}
                    </div>
                    <ChevronRight
                        className="size-4 shrink-0 text-muted-foreground"
                        aria-hidden
                    />
                </Link>

                <div className="border-t">
                    <Button
                        variant="ghost"
                        className="h-12 w-full justify-start gap-3 rounded-none px-4 text-destructive hover:bg-destructive/5 hover:text-destructive"
                        asChild
                        onClick={onNavigate}
                    >
                        <Link href={logout()} as="button" prefetch={false}>
                            <LogOut className="size-5 shrink-0" />
                            Log out
                        </Link>
                    </Button>
                </div>
            </div>
        </div>
    );
}
