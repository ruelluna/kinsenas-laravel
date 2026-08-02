type SurveyInterstitialProps = {
    message: string;
};

export default function SurveyInterstitial({
    message,
}: SurveyInterstitialProps) {
    return (
        <div className="flex flex-1 flex-col justify-center gap-6 py-4">
            <div className="rounded-3xl border border-primary/15 bg-linear-to-br from-primary/8 via-background to-allocation-4/10 px-6 py-8 text-center shadow-xs">
                <p className="text-lg leading-relaxed font-medium text-pretty sm:text-xl">
                    {message}
                </p>
            </div>
        </div>
    );
}
