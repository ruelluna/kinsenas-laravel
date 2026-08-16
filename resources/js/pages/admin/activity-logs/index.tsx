import { Form, Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type ActivityRow = {
    id: number;
    description: string;
    event: string | null;
    eventLabel: string | null;
    causerName: string | null;
    causerEmail: string | null;
    teamName: string | null;
    teamId: string | null;
    createdAt: string | null;
};

type PaginatedActivities = {
    data: ActivityRow[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

type EventOption = {
    value: string;
    label: string;
};

type Props = {
    activities: PaginatedActivities;
    filters: {
        search?: string | null;
        event?: string | null;
        team_id?: string | null;
    };
    events: EventOption[];
};

export default function AdminActivityLogsIndex({
    activities,
    filters,
    events,
}: Props) {
    return (
        <>
            <Head title="Admin — Activity logs" />
            <Heading
                variant="small"
                title="Activity logs"
                description="Who did what across Kinsenas. Financial amounts are never stored here."
            />

            <Form
                method="get"
                action="/admin/activity-logs"
                className="mt-6 flex flex-wrap gap-3"
            >
                <div className="grid gap-2">
                    <Label htmlFor="search">Search</Label>
                    <Input
                        id="search"
                        name="search"
                        defaultValue={filters.search ?? ''}
                        placeholder="Description, name, or email"
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="event">Event</Label>
                    <select
                        id="event"
                        name="event"
                        defaultValue={filters.event ?? ''}
                        className="h-9 rounded-md border border-input px-3 text-sm"
                    >
                        <option value="">All events</option>
                        {events.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="team_id">Team ID</Label>
                    <Input
                        id="team_id"
                        name="team_id"
                        defaultValue={filters.team_id ?? ''}
                        placeholder="UUID"
                    />
                </div>
                <div className="flex items-end">
                    <Button type="submit" variant="outline">
                        Filter
                    </Button>
                </div>
            </Form>

            <div className="mt-6 space-y-3">
                {activities.data.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        No activity logs found.
                    </p>
                ) : (
                    activities.data.map((activity) => (
                        <div
                            key={activity.id}
                            className="rounded-lg border p-4 text-sm"
                            data-test="activity-log-row"
                        >
                            <div className="font-medium">
                                {activity.description}
                            </div>
                            <div className="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-muted-foreground">
                                {activity.eventLabel ? (
                                    <span>{activity.eventLabel}</span>
                                ) : null}
                                {activity.causerName ? (
                                    <span>{activity.causerName}</span>
                                ) : (
                                    <span>System</span>
                                )}
                                {activity.teamName ? (
                                    <span>{activity.teamName}</span>
                                ) : null}
                                {activity.createdAt ? (
                                    <span>
                                        {new Date(
                                            activity.createdAt,
                                        ).toLocaleString()}
                                    </span>
                                ) : null}
                            </div>
                        </div>
                    ))
                )}
            </div>
        </>
    );
}
