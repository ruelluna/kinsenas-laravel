type Props = {
    name: string;
    className?: string;
};

export default function ContentByline({ name, className }: Props) {
    return (
        <p className={className ?? 'text-sm text-muted-foreground'}>
            By {name}
        </p>
    );
}
