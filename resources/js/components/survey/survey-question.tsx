import type { SurveyQuestion as SurveyQuestionType } from '@/lib/survey/survey-types';
import SurveyOptionCard from './survey-option-card';

type SurveyQuestionProps = {
    question: SurveyQuestionType;
    value: string | string[] | undefined;
    multiSelectHint: string;
    onChange: (value: string | string[]) => void;
};

export default function SurveyQuestion({
    question,
    value,
    multiSelectHint,
    onChange,
}: SurveyQuestionProps) {
    const isMulti = question.type === 'multi';
    const selectedValues = isMulti ? (Array.isArray(value) ? value : []) : [];

    const handleSelect = (optionValue: string) => {
        if (isMulti) {
            const next = selectedValues.includes(optionValue)
                ? selectedValues.filter((item) => item !== optionValue)
                : [...selectedValues, optionValue];

            onChange(next);

            return;
        }

        onChange(optionValue);
    };

    return (
        <div className="flex flex-1 flex-col gap-5">
            <div className="space-y-2">
                <h2 className="text-xl font-semibold tracking-tight text-balance sm:text-2xl">
                    {question.prompt}
                </h2>
                {question.skipNote && (
                    <p className="text-sm text-pretty text-muted-foreground">
                        {question.skipNote}
                    </p>
                )}
                {isMulti && (
                    <p className="text-sm text-muted-foreground">
                        {multiSelectHint}
                    </p>
                )}
            </div>

            <div className="flex flex-col gap-2.5">
                {question.options.map((option) => (
                    <SurveyOptionCard
                        key={option.value}
                        label={option.label}
                        mode={question.type}
                        selected={
                            isMulti
                                ? selectedValues.includes(option.value)
                                : value === option.value
                        }
                        onSelect={() => handleSelect(option.value)}
                    />
                ))}
            </div>
        </div>
    );
}
