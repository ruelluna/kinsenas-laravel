export type SurveyLanguage = 'en' | 'tl' | 'ceb';

export type QuestionId =
    'q1' | 'q2' | 'q3' | 'q4' | 'q5' | 'q6' | 'q7' | 'q8' | 'q9' | 'q10';

export type SurveyStep =
    | 'language'
    | 'intro'
    | `question-${1 | 2 | 3 | 4 | 5 | 6 | 7 | 8 | 9 | 10}`
    | 'interstitial-1'
    | 'interstitial-2'
    | 'loading'
    | 'result'
    | 'thank-you';

export type ResultSlug =
    | 'family-first-planner'
    | 'faith-giving-planner'
    | 'bills-debt-organizer'
    | 'goal-builder'
    | 'transfer-tracker'
    | 'discipline-builder'
    | 'payday-planner';

export type SurveyAnswers = Partial<Record<QuestionId, string | string[]>>;

export type SurveyOption = {
    value: string;
    label: string;
};

export type SurveyQuestion = {
    id: QuestionId;
    type: 'single' | 'multi';
    prompt: string;
    skipNote?: string;
    options: SurveyOption[];
};

export type SurveyResultCopy = {
    title: string;
    description: string;
};

export type SurveyLanguageContent = {
    languageLabel: string;
    intro: string;
    privacyNote: string;
    progressLabel: (current: number, total: number) => string;
    back: string;
    continue: string;
    multiSelectHint: string;
    resultPreviewLabel: string;
    thankYouTitle: string;
    interstitials: {
        afterQ3: string;
        afterQ6: string;
    };
    loadingSteps: [string, string, string];
    loadingTitle: string;
    loadingSubtitle: string;
    resultCTA: {
        headline: string;
        emailLabel: string;
        nameLabel: string;
        namePlaceholder: string;
        submit: string;
        emailRequired: string;
        emailInvalid: string;
        submitError: string;
        learnLink: string;
    };
    thankYou: string;
    questions: SurveyQuestion[];
    results: Record<ResultSlug, SurveyResultCopy>;
};

export type SurveySubmissionPayload = {
    language: SurveyLanguage;
    completed_at: string;
    answers: SurveyAnswers;
    result: ResultSlug;
    email: string;
    name: string;
};
