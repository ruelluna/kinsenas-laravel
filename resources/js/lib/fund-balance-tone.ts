export function remainingTone(percentUsed: number | null): string {
    if (percentUsed === null) {
        return 'text-muted-foreground';
    }

    if (percentUsed >= 90) {
        return 'text-destructive';
    }

    if (percentUsed >= 70) {
        return 'text-warning';
    }

    return 'text-success';
}
