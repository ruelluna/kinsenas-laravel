import { Button } from '@/components/ui/button';

type SurveyNavProps = {
    backLabel: string;
    continueLabel: string;
    canContinue: boolean;
    showBack: boolean;
    showContinue: boolean;
    onBack: () => void;
    onContinue: () => void;
};

export default function SurveyNav({
    backLabel,
    continueLabel,
    canContinue,
    showBack,
    showContinue,
    onBack,
    onContinue,
}: SurveyNavProps) {
    if (!showBack && !showContinue) {
        return null;
    }

    return (
        <div className="sticky bottom-0 -mx-6 mt-8 border-t border-border/40 bg-background/95 px-6 py-4 backdrop-blur-md">
            <div className="flex gap-3">
                {showBack && (
                    <Button type="button" variant="outline" className="h-11 flex-1 rounded-full" onClick={onBack}>
                        {backLabel}
                    </Button>
                )}
                {showContinue && (
                    <Button
                        type="button"
                        className="h-11 flex-1 rounded-full"
                        disabled={!canContinue}
                        onClick={onContinue}
                    >
                        {continueLabel}
                    </Button>
                )}
            </div>
        </div>
    );
}
