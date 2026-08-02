<?php

namespace App\Support\Survey;

use App\Models\SurveyResponse;

final class SurveyGhlTagBuilder
{
    /**
     * @return list<string>
     */
    public static function from(SurveyResponse $surveyResponse): array
    {
        $tags = [
            'kinsenas-survey',
            'survey-completed',
            'survey-lang-'.$surveyResponse->language->value,
            'survey-result-'.$surveyResponse->result->value,
        ];

        $answers = $surveyResponse->answers ?? [];

        foreach (SurveyAnswerOptions::QUESTION_IDS as $questionId) {
            if (! array_key_exists($questionId, $answers)) {
                continue;
            }

            $value = $answers[$questionId];

            if (is_array($value)) {
                foreach ($value as $option) {
                    if (! is_string($option) || $option === '') {
                        continue;
                    }

                    $tags[] = "survey-{$questionId}-{$option}";
                }

                continue;
            }

            if (! is_string($value) || $value === '') {
                continue;
            }

            $tags[] = "survey-{$questionId}-{$value}";
        }

        return array_values(array_unique($tags));
    }
}
