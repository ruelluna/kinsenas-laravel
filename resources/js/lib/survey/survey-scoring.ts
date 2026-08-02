import type { QuestionId, ResultSlug, SurveyAnswers } from './survey-types';

function includesOption(
    answers: SurveyAnswers,
    questionId: QuestionId,
    value: string,
): boolean {
    const answer = answers[questionId];

    if (Array.isArray(answer)) {
        return answer.includes(value);
    }

    return answer === value;
}

function hasAnyQ8Habit(answers: SurveyAnswers): boolean {
    const answer = answers.q8;

    if (!Array.isArray(answer) || answer.length === 0) {
        return false;
    }

    return answer.some(
        (value) => value !== 'none' && value !== 'prefer_not_to_say',
    );
}

/**
 * First-match priority when multiple result rules apply.
 */
export function resolveSurveyResult(answers: SurveyAnswers): ResultSlug {
    if (
        includesOption(answers, 'q7', 'forgetting_transfers') ||
        includesOption(answers, 'q9', 'track_transfers')
    ) {
        return 'transfer-tracker';
    }

    if (
        (answers.q3 !== undefined && answers.q3 !== 'none') ||
        includesOption(answers, 'q5', 'family_support') ||
        includesOption(answers, 'q9', 'family_obligations')
    ) {
        return 'family-first-planner';
    }

    if (
        includesOption(answers, 'q5', 'church_giving') ||
        includesOption(answers, 'q9', 'plan_giving')
    ) {
        return 'faith-giving-planner';
    }

    if (
        includesOption(answers, 'q5', 'bills') ||
        includesOption(answers, 'q5', 'debt') ||
        includesOption(answers, 'q7', 'debt') ||
        includesOption(answers, 'q7', 'too_many_bills') ||
        includesOption(answers, 'q4', 'pay_bills') ||
        includesOption(answers, 'q4', 'pay_debt')
    ) {
        return 'bills-debt-organizer';
    }

    if (
        includesOption(answers, 'q5', 'savings') ||
        includesOption(answers, 'q5', 'personal_goals') ||
        includesOption(answers, 'q9', 'save_goals')
    ) {
        return 'goal-builder';
    }

    if (
        includesOption(answers, 'q4', 'spend_first') ||
        includesOption(answers, 'q4', 'no_routine') ||
        includesOption(answers, 'q7', 'impulse_spending') ||
        hasAnyQ8Habit(answers)
    ) {
        return 'discipline-builder';
    }

    return 'payday-planner';
}
