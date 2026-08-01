type SurveyIntroProps = {
    intro: string;
};

export default function SurveyIntro({ intro }: SurveyIntroProps) {
    return (
        <div className="flex flex-1 flex-col justify-center gap-4 text-center">
            <h1 className="text-balance text-2xl font-semibold tracking-tight sm:text-3xl">{intro}</h1>
        </div>
    );
}
