import { CheckCircle2 } from 'lucide-react';

type SurveyThankYouProps = {
    title: string;
    message: string;
};

export default function SurveyThankYou({ title, message }: SurveyThankYouProps) {
    return (
        <div className="flex flex-1 flex-col items-center justify-center gap-5 py-8 text-center">
            <span className="flex size-14 items-center justify-center rounded-full bg-success/15 text-success">
                <CheckCircle2 className="size-7" />
            </span>
            <h2 className="text-balance text-2xl font-semibold tracking-tight">{title}</h2>
            <p className="max-w-sm text-pretty leading-relaxed text-muted-foreground">{message}</p>
        </div>
    );
}
