import {
    BANK_ACCOUNTS,
    SAMPLE_INCOME,
} from '@/components/marketing/landing-content';
import {
    landingFundRowClassName,
    landingLogoBadgeClassName,
} from '@/components/marketing/landing-surface';
import { BankOptionLogo } from '@/components/savings/bank-option-select';
import { formatMoney } from '@/lib/format-money';

export default function LandingBanks() {
    return (
        <section id="banks" className="mx-auto max-w-7xl px-6 py-24">
            <div className="mb-16 md:flex md:items-end md:justify-between">
                <div>
                    <h2 className="mb-4 font-space text-4xl font-bold text-foreground">
                        Your banks, your buckets.
                    </h2>
                    <p className="max-w-xl text-muted-foreground">
                        See every peso across your accounts and the fund it
                        belongs to — all in one glance.
                    </p>
                </div>
                <div className="mt-6 rounded-2xl border border-primary/25 bg-primary/5 px-6 py-4 md:mt-0">
                    <p className="text-xs font-bold tracking-widest text-primary uppercase">
                        Total across accounts
                    </p>
                    <p className="font-space text-2xl font-bold text-primary">
                        {formatMoney(SAMPLE_INCOME)}
                    </p>
                </div>
            </div>

            <div className="grid gap-6 md:grid-cols-2">
                {BANK_ACCOUNTS.map((account) => (
                    <div
                        key={account.name}
                        className="rounded-3xl border border-border bg-surface p-6 transition-colors hover:border-primary/30"
                    >
                        <div className="mb-6 flex items-start justify-between">
                            <div className="flex items-center gap-3">
                                <div
                                    className={`${landingLogoBadgeClassName} h-11 w-11`}
                                >
                                    <BankOptionLogo
                                        bank={{
                                            id: account.name,
                                            name: account.name,
                                            logoUrl: account.logoUrl,
                                        }}
                                        className="size-8"
                                    />
                                </div>
                                <div>
                                    <h3 className="font-space text-lg font-bold text-foreground">
                                        {account.name}
                                    </h3>
                                    <p className="text-xs text-muted-foreground">
                                        {account.type}
                                    </p>
                                </div>
                            </div>
                            <span className="font-space text-xl font-bold text-foreground">
                                {formatMoney(account.total)}
                            </span>
                        </div>

                        <div className="space-y-3">
                            {account.funds.map((fund) => (
                                <div
                                    key={fund.label}
                                    className={landingFundRowClassName}
                                >
                                    <span className="text-sm text-muted-foreground">
                                        {fund.label}
                                    </span>
                                    <span
                                        className={`font-space font-bold ${fund.textClass}`}
                                    >
                                        {formatMoney(fund.amount)}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>
                ))}
            </div>
        </section>
    );
}
