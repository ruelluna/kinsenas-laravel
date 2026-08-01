import { Lock } from 'lucide-react';

export default function LandingTrustStrip() {
    return (
        <div className="border-y border-white/10 bg-black/70 px-6 py-4 backdrop-blur-sm lg:px-10">
            <div className="mx-auto flex max-w-6xl flex-col items-center gap-2 text-center sm:flex-row sm:justify-center sm:gap-3">
                <Lock className="size-4 shrink-0 text-primary" aria-hidden />
                <p className="text-sm leading-relaxed text-white/85 sm:text-left">
                    <span className="font-medium text-white">
                        Your income is encrypted and safe.
                    </span>{' '}
                    Only you can unlock your financial data — not Kinsenas
                    staff, not admins.{' '}
                    <a
                        href="#privacy"
                        className="font-medium text-primary underline-offset-4 hover:underline"
                    >
                        Learn how it works
                    </a>
                </p>
            </div>
        </div>
    );
}
