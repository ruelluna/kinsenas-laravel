import {
    getLanguageDisplayName,
    SURVEY_LANGUAGES,
} from '@/lib/survey/survey-content';
import type { SurveyLanguage } from '@/lib/survey/survey-types';
import SurveyOptionCard from './survey-option-card';

type SurveyLanguageSelectProps = {
    onSelect: (language: SurveyLanguage) => void;
};

export default function SurveyLanguageSelect({
    onSelect,
}: SurveyLanguageSelectProps) {
    return (
        <div className="flex flex-1 flex-col gap-6">
            <div className="space-y-3 text-center">
                <p className="inline-flex rounded-full bg-primary/8 px-3 py-1 text-sm font-medium text-primary">
                    Kinsenas Survey
                </p>
                <h1 className="text-2xl font-semibold tracking-tight text-balance sm:text-3xl">
                    Choose your language
                </h1>
                <p className="text-pretty text-muted-foreground">
                    Piliin ang wika mo · Pilia ang imong pinulongan
                </p>
            </div>

            <div className="flex flex-col gap-3">
                {SURVEY_LANGUAGES.map((language) => (
                    <SurveyOptionCard
                        key={language}
                        label={getLanguageDisplayName(language)}
                        selected={false}
                        mode="single"
                        onSelect={() => onSelect(language)}
                    />
                ))}
            </div>
        </div>
    );
}
