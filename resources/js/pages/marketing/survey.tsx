import { Head, usePage } from '@inertiajs/react';
import { useCallback, useMemo, useState } from 'react';
import SurveyIntro from '@/components/survey/survey-intro';
import SurveyInterstitial from '@/components/survey/survey-interstitial';
import SurveyLanguageSelect from '@/components/survey/survey-language-select';
import SurveyLoading from '@/components/survey/survey-loading';
import SurveyNav from '@/components/survey/survey-nav';
import SurveyQuestion from '@/components/survey/survey-question';
import SurveyResult from '@/components/survey/survey-result';
import SurveyShell from '@/components/survey/survey-shell';
import SurveyThankYou from '@/components/survey/survey-thank-you';
import { getSurveyContent } from '@/lib/survey/survey-content';
import {
    getNextStep,
    getPreviousStep,
    getQuestionNumber,
    showsBackButton,
    showsContinueButton,
} from '@/lib/survey/survey-navigation';
import { resolveSurveyResult } from '@/lib/survey/survey-scoring';
import type {
    QuestionId,
    ResultSlug,
    SurveyAnswers,
    SurveyLanguage,
    SurveyStep,
    SurveySubmissionPayload,
} from '@/lib/survey/survey-types';
import { cn } from '@/lib/utils';

function questionIdFromStep(step: SurveyStep): QuestionId | null {
    const number = getQuestionNumber(step);

    if (!number) {
        return null;
    }

    return `q${number}` as QuestionId;
}

function isStepValid(step: SurveyStep, answers: SurveyAnswers): boolean {
    if (step === 'intro') {
        return true;
    }

    if (step === 'interstitial-1' || step === 'interstitial-2') {
        return true;
    }

    const questionId = questionIdFromStep(step);

    if (!questionId) {
        return true;
    }

    const answer = answers[questionId];

    if (questionId === 'q8') {
        return true;
    }

    if (questionId === 'q5') {
        return Array.isArray(answer) && answer.length > 0;
    }

    return typeof answer === 'string' && answer.length > 0;
}

export default function Survey() {
    const { name } = usePage().props;

    const [step, setStep] = useState<SurveyStep>('language');
    const [language, setLanguage] = useState<SurveyLanguage | null>(null);
    const [answers, setAnswers] = useState<SurveyAnswers>({});
    const [resultSlug, setResultSlug] = useState<ResultSlug | null>(null);

    const content = useMemo(
        () => (language ? getSurveyContent(language) : null),
        [language],
    );

    const questionNumber = getQuestionNumber(step);
    const currentQuestionId = questionIdFromStep(step);
    const currentQuestion = content && currentQuestionId
        ? content.questions.find((question) => question.id === currentQuestionId)
        : undefined;

    const handleLanguageSelect = (selectedLanguage: SurveyLanguage) => {
        setLanguage(selectedLanguage);
        setStep('intro');
    };

    const handleContinue = () => {
        if (step === 'question-10') {
            const result = resolveSurveyResult(answers);
            setResultSlug(result);
            setStep('loading');
            return;
        }

        const next = getNextStep(step);

        if (next) {
            setStep(next);
        }
    };

    const handleBack = () => {
        const previous = getPreviousStep(step);

        if (previous) {
            setStep(previous);
        }
    };

    const handleAnswerChange = (value: string | string[]) => {
        if (!currentQuestionId) {
            return;
        }

        setAnswers((previous) => ({
            ...previous,
            [currentQuestionId]: value,
        }));
    };

    const handleLoadingComplete = useCallback(() => {
        setStep('result');
    }, []);

    const handleResultSubmit = ({ email, name: respondentName }: { email: string; name: string }) => {
        if (!language || !resultSlug) {
            return;
        }

        const payload: SurveySubmissionPayload = {
            language,
            completedAt: new Date().toISOString(),
            answers,
            result: resultSlug,
            email,
            name: respondentName,
        };

        // Future: POST /survey/responses with validated payload + optional GHL webhook.
        console.log('[Kinsenas Survey]', payload);

        setStep('thank-you');
    };

    const showPrivacyNote = step === 'intro' || step === 'result';
    const canContinue = isStepValid(step, answers);

    const renderStep = () => {
        if (step === 'language') {
            return <SurveyLanguageSelect onSelect={handleLanguageSelect} />;
        }

        if (!content) {
            return null;
        }

        switch (step) {
            case 'intro':
                return <SurveyIntro intro={content.intro} />;
            case 'interstitial-1':
                return <SurveyInterstitial message={content.interstitials.afterQ3} />;
            case 'interstitial-2':
                return <SurveyInterstitial message={content.interstitials.afterQ6} />;
            case 'loading':
                return (
                    <SurveyLoading
                        steps={content.loadingSteps}
                        title={content.loadingTitle}
                        subtitle={content.loadingSubtitle}
                        onComplete={handleLoadingComplete}
                    />
                );
            case 'result':
                return resultSlug ? (
                    <SurveyResult
                        resultSlug={resultSlug}
                        content={content}
                        onSubmit={handleResultSubmit}
                    />
                ) : null;
            case 'thank-you':
                return <SurveyThankYou title={content.thankYouTitle} message={content.thankYou} />;
            default:
                if (currentQuestion) {
                    return (
                        <SurveyQuestion
                            question={currentQuestion}
                            value={answers[currentQuestion.id]}
                            multiSelectHint={content.multiSelectHint}
                            onChange={handleAnswerChange}
                        />
                    );
                }

                return null;
        }
    };

    return (
        <>
            <Head title="Payday plan survey" />
            <SurveyShell
                appName={name}
                step={step}
                questionNumber={questionNumber}
                progressLabel={content?.progressLabel ?? ((current, total) => `Question ${current} of ${total}`)}
                privacyNote={showPrivacyNote && content ? content.privacyNote : undefined}
            >
                <div
                    key={step}
                    className={cn(
                        'flex flex-1 flex-col motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-2 motion-safe:duration-300',
                    )}
                >
                    {renderStep()}
                </div>

                {content && (
                    <SurveyNav
                        backLabel={content.back}
                        continueLabel={content.continue}
                        canContinue={canContinue}
                        showBack={showsBackButton(step)}
                        showContinue={showsContinueButton(step)}
                        onBack={handleBack}
                        onContinue={handleContinue}
                    />
                )}
            </SurveyShell>
        </>
    );
}
