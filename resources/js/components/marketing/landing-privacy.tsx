import { Lock } from 'lucide-react';

export default function LandingPrivacy() {
    return (
        <section id="security" className="mx-auto max-w-7xl px-6 py-24">
            <div className="flex flex-col items-center gap-12 rounded-[3rem] bg-primary p-12 text-primary-foreground md:flex-row md:p-20">
                <div className="flex-1">
                    <h2 className="mb-6 font-space text-4xl font-bold md:text-5xl">
                        Your numbers are for your eyes only.
                    </h2>
                    <p className="mb-8 max-w-lg text-lg font-medium opacity-80">
                        Unlike other apps, Kinsenas encrypts your data on your
                        device. We never see your income, your bank names, or
                        your spending. It’s all sealed in your private vault.
                    </p>
                    <div className="flex flex-wrap gap-4">
                        <span className="rounded-full border border-midnight/10 bg-midnight/10 px-4 py-2 text-xs font-bold">
                            Client-side encryption
                        </span>
                        <span className="rounded-full border border-midnight/10 bg-midnight/10 px-4 py-2 text-xs font-bold">
                            Recovery key on signup
                        </span>
                    </div>
                </div>
                <div className="grid aspect-square w-full place-items-center rounded-3xl border border-midnight/10 bg-midnight/5 md:w-1/3">
                    <Lock className="h-16 w-16" strokeWidth={1.5} />
                </div>
            </div>
        </section>
    );
}
