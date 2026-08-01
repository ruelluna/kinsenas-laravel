import type { SurveyStep } from './survey-types';

const STEP_ORDER: SurveyStep[] = [
    'language',
    'intro',
    'question-1',
    'question-2',
    'question-3',
    'interstitial-1',
    'question-4',
    'question-5',
    'question-6',
    'interstitial-2',
    'question-7',
    'question-8',
    'question-9',
    'question-10',
    'loading',
    'result',
    'thank-you',
];

export function getNextStep(step: SurveyStep): SurveyStep | null {
    const index = STEP_ORDER.indexOf(step);

    if (index === -1 || index >= STEP_ORDER.length - 1) {
        return null;
    }

    return STEP_ORDER[index + 1];
}

export function getPreviousStep(step: SurveyStep): SurveyStep | null {
    const index = STEP_ORDER.indexOf(step);

    if (index <= 0) {
        return null;
    }

    return STEP_ORDER[index - 1];
}

export function getQuestionNumber(step: SurveyStep): number | null {
    const match = step.match(/^question-(\d+)$/);

    if (!match) {
        return null;
    }

    return Number(match[1]);
}

export function showsProgress(step: SurveyStep): boolean {
    return getQuestionNumber(step) !== null;
}

export function showsBackButton(step: SurveyStep): boolean {
    return step !== 'language' && step !== 'loading' && step !== 'thank-you';
}

export function showsContinueButton(step: SurveyStep): boolean {
    return (
        step === 'intro' ||
        step.startsWith('question-') ||
        step === 'interstitial-1' ||
        step === 'interstitial-2'
    );
}
