import { router } from '@inertiajs/react';
import { cn } from '@/lib/utils';

export type LearnFilter =
    | 'all'
    | 'series'
    | 'reminders'
    | 'articles'
    | 'side-hustles'
    | 'podcasts';

const pillBase =
    'rounded-md px-3 py-1.5 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2';

const pillActive = 'bg-primary text-primary-foreground shadow-sm';

const pillInactive = 'text-muted-foreground hover:bg-background/80 hover:text-foreground';

const memberTabs: Array<{ value: LearnFilter; label: string }> = [
    { value: 'all', label: 'All' },
    { value: 'series', label: 'Series' },
    { value: 'reminders', label: 'Reminders' },
    { value: 'articles', label: 'Articles' },
    { value: 'side-hustles', label: 'Side hustles' },
    { value: 'podcasts', label: 'Podcasts' },
];

const guestTabs: Array<{ value: LearnFilter; label: string }> = [
    { value: 'all', label: 'All' },
    { value: 'side-hustles', label: 'Side hustles' },
    { value: 'podcasts', label: 'Podcasts' },
];

type Props = {
    filter: LearnFilter;
    hasFullAccess: boolean;
};

export default function LearnNavTabs({ filter, hasFullAccess }: Props) {
    const tabs = hasFullAccess ? memberTabs : guestTabs;

    return (
        <nav aria-label="Learn">
            <div
                role="group"
                aria-label="Browse"
                className="inline-flex flex-wrap gap-1 rounded-lg border bg-muted/40 p-1"
            >
                {tabs.map((tab) => (
                    <button
                        key={tab.value}
                        type="button"
                        data-test={`learn-filter-${tab.value}`}
                        className={cn(
                            pillBase,
                            filter === tab.value ? pillActive : pillInactive,
                        )}
                        aria-current={filter === tab.value ? 'true' : undefined}
                        onClick={() =>
                            router.get(
                                '/learn',
                                tab.value === 'all' ? {} : { filter: tab.value },
                                { preserveState: true },
                            )
                        }
                    >
                        {tab.label}
                    </button>
                ))}
            </div>
        </nav>
    );
}

export { pillActive, pillBase, pillInactive };
