import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import type { AdminBetaFeedback } from '@/types/billing';

type Props = {
    feedbacks: AdminBetaFeedback[];
};

export default function AdminBetaFeedbackIndex({ feedbacks }: Props) {
    return (
        <>
            <Head title="Admin — Beta feedback" />
            <Heading
                variant="small"
                title="Beta feedback"
                description="Feedback submitted during the open beta."
            />

            <div className="mt-6 space-y-4">
                {feedbacks.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        No feedback submitted yet.
                    </p>
                ) : (
                    feedbacks.map((feedback) => (
                        <article
                            key={feedback.id}
                            className="rounded-lg border p-4 text-sm"
                        >
                            <div className="flex flex-wrap items-center gap-x-3 gap-y-1 text-muted-foreground">
                                <span className="font-medium text-foreground">
                                    {feedback.userName ?? 'Unknown user'}
                                </span>
                                {feedback.userEmail && (
                                    <span>{feedback.userEmail}</span>
                                )}
                                {feedback.teamName && (
                                    <span>{feedback.teamName}</span>
                                )}
                                {feedback.categoryLabel && (
                                    <span className="rounded-full bg-muted px-2 py-0.5 text-xs">
                                        {feedback.categoryLabel}
                                    </span>
                                )}
                                <span>
                                    {new Date(
                                        feedback.createdAt,
                                    ).toLocaleString()}
                                </span>
                            </div>
                            <p className="mt-3 whitespace-pre-wrap text-foreground">
                                {feedback.message}
                            </p>
                        </article>
                    ))
                )}
            </div>
        </>
    );
}
