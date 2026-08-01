import { FOOTER_TAGLINES } from '@/components/marketing/landing-content';

type LandingFooterProps = {
    appName: string;
};

export default function LandingFooter({ appName }: LandingFooterProps) {
    return (
        <footer className="border-t border-border/40 px-6 py-10 lg:px-10">
            <div className="mx-auto flex max-w-6xl flex-col items-center gap-6 text-center">
                <div className="flex max-w-2xl flex-wrap justify-center gap-2">
                    {FOOTER_TAGLINES.map((tagline) => (
                        <span
                            key={tagline}
                            className="rounded-full bg-muted/60 px-3 py-1 text-xs text-muted-foreground"
                        >
                            {tagline}
                        </span>
                    ))}
                </div>
                <p className="text-xs text-muted-foreground">
                    © {new Date().getFullYear()} {appName}. Built for thoughtful
                    payday habits.
                </p>
            </div>
        </footer>
    );
}
