import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { cn } from '@/lib/utils';

type Props = {
    name: string;
    avatarUrl?: string | null;
    className?: string;
};

function initials(name: string): string {
    return name
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() ?? '')
        .join('');
}

export default function ContentByline({ name, avatarUrl = null, className }: Props) {
    return (
        <div className={cn('flex items-center gap-3', className)}>
            <Avatar className="size-9">
                {avatarUrl ? <AvatarImage src={avatarUrl} alt={name} /> : null}
                <AvatarFallback>{initials(name)}</AvatarFallback>
            </Avatar>
            <p className="text-sm text-muted-foreground">By {name}</p>
        </div>
    );
}
