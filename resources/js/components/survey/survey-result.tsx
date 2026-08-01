import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ALLOCATION_BORDER_CLASSES } from '@/components/marketing/landing-content';
import { RESULT_ALLOCATION_INDEX } from '@/lib/survey/survey-content';
import type { ResultSlug, SurveyLanguageContent } from '@/lib/survey/survey-types';
import { cn } from '@/lib/utils';

type SurveyResultProps = {
    resultSlug: ResultSlug;
    content: SurveyLanguageContent;
    onSubmit: (payload: { email: string; name: string }) => void;
};

const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export default function SurveyResult({ resultSlug, content, onSubmit }: SurveyResultProps) {
    const [email, setEmail] = useState('');
    const [name, setName] = useState('');
    const [emailError, setEmailError] = useState<string | null>(null);

    const result = content.results[resultSlug];
    const allocationIndex = RESULT_ALLOCATION_INDEX[resultSlug];

    const handleSubmit = (event: React.FormEvent) => {
        event.preventDefault();

        const trimmedEmail = email.trim();

        if (!trimmedEmail) {
            setEmailError(content.resultCTA.emailRequired);
            return;
        }

        if (!EMAIL_PATTERN.test(trimmedEmail)) {
            setEmailError(content.resultCTA.emailInvalid);
            return;
        }

        setEmailError(null);
        onSubmit({ email: trimmedEmail, name: name.trim() });
    };

    return (
        <div className="flex flex-1 flex-col gap-6">
            <div
                className={cn(
                    'rounded-3xl border border-border/40 bg-card/60 px-5 py-6 shadow-xs',
                    ALLOCATION_BORDER_CLASSES[allocationIndex],
                    'border-l-4',
                )}
            >
                <p className="mb-2 text-sm font-medium text-primary">{content.resultPreviewLabel}</p>
                <h2 className="text-balance text-2xl font-semibold tracking-tight">{result.title}</h2>
                <p className="mt-3 text-pretty leading-relaxed text-muted-foreground">{result.description}</p>
            </div>

            <form onSubmit={handleSubmit} className="flex flex-col gap-4">
                <div className="space-y-2">
                    <h3 className="text-balance text-lg font-semibold">{content.resultCTA.headline}</h3>
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="survey-email">{content.resultCTA.emailLabel}</Label>
                    <Input
                        id="survey-email"
                        type="email"
                        autoComplete="email"
                        value={email}
                        onChange={(event) => {
                            setEmail(event.target.value);
                            if (emailError) {
                                setEmailError(null);
                            }
                        }}
                        aria-invalid={emailError ? true : undefined}
                    />
                    {emailError && <p className="text-sm text-destructive">{emailError}</p>}
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="survey-name">{content.resultCTA.nameLabel}</Label>
                    <Input
                        id="survey-name"
                        type="text"
                        autoComplete="name"
                        placeholder={content.resultCTA.namePlaceholder}
                        value={name}
                        onChange={(event) => setName(event.target.value)}
                    />
                </div>

                <Button type="submit" size="lg" className="mt-2 h-11 rounded-full">
                    {content.resultCTA.submit}
                </Button>
            </form>
        </div>
    );
}
