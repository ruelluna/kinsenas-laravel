import type {
    ResultSlug,
    SurveyLanguage,
    SurveyLanguageContent,
    SurveyQuestion,
} from './survey-types';

const QUESTION_OPTIONS = {
    q1: [
        { value: 'employee', en: 'Employee', tl: 'Empleyado', ceb: 'Empleyado' },
        { value: 'freelancer', en: 'Freelancer', tl: 'Freelancer', ceb: 'Freelancer' },
        { value: 'business_owner', en: 'Business owner', tl: 'May negosyo', ceb: 'Naay negosyo' },
        { value: 'ofw', en: 'OFW', tl: 'OFW', ceb: 'OFW' },
        { value: 'student', en: 'Student with income or allowance', tl: 'Estudyante na may income o allowance', ceb: 'Estudyante nga naay income o allowance' },
        { value: 'couple_family', en: 'Couple/family managing shared money', tl: 'Couple/pamilya na nagma-manage ng shared money', ceb: 'Couple/pamilya nga nag-manage ug shared money' },
        { value: 'other', en: 'Other', tl: 'Iba pa', ceb: 'Uban pa' },
    ],
    q2: [
        { value: 'single', en: 'Single', tl: 'Single', ceb: 'Single' },
        { value: 'married', en: 'Married', tl: 'Kasal', ceb: 'Minyo' },
        { value: 'relationship_shared', en: 'In a relationship and sharing expenses', tl: 'May partner at may shared expenses', ceb: 'Naay partner ug nag-share sa expenses' },
        { value: 'parent', en: 'Parent', tl: 'Magulang/parent', ceb: 'Ginikanan/parent' },
        { value: 'supporting_family', en: 'Supporting family', tl: 'Sumusuporta sa pamilya', ceb: 'Nagsuporta sa pamilya' },
        { value: 'living_independently', en: 'Living independently', tl: 'Namumuhay independently', ceb: 'Nagpuyo independently' },
        { value: 'living_with_family', en: 'Living with family', tl: 'Nakatira kasama ang pamilya', ceb: 'Nagpuyo uban sa pamilya' },
    ],
    q3: [
        { value: 'none', en: 'None', tl: 'Wala', ceb: 'Wala' },
        { value: '1', en: '1', tl: '1', ceb: '1' },
        { value: '2-3', en: '2-3', tl: '2-3', ceb: '2-3' },
        { value: '4-5', en: '4-5', tl: '4-5', ceb: '4-5' },
        { value: '6+', en: '6 or more', tl: '6 pataas', ceb: '6 pataas' },
    ],
    q4: [
        { value: 'pay_bills', en: 'I pay bills', tl: 'Nagbabayad ako ng bills', ceb: 'Mobayad ko sa bills' },
        { value: 'send_family', en: 'I send money to family', tl: 'Nagpapadala ako sa pamilya', ceb: 'Mopadala ko sa pamilya' },
        { value: 'set_savings', en: 'I set aside savings', tl: 'Nagtatabi ako para sa savings', ceb: 'Mag-set aside ko para savings' },
        { value: 'give_tithe', en: 'I give/tithe', tl: 'Nagbibigay/tithe ako', ceb: 'Mohatag/tithe ko' },
        { value: 'pay_debt', en: 'I pay debt', tl: 'Nagbabayad ako ng utang', ceb: 'Mobayad ko sa utang' },
        { value: 'spend_first', en: 'I spend first, then plan what remains', tl: 'Gumagastos muna ako, tapos pinaplano ang natira', ceb: 'Mogasto una ko, unya planohon ang mahabilin' },
        { value: 'no_routine', en: 'I do not have a clear routine yet', tl: 'Wala pa akong malinaw na routine', ceb: 'Wala pa koy klaro nga routine' },
    ],
    q5: [
        { value: 'bills', en: 'Bills', tl: 'Bills', ceb: 'Bills' },
        { value: 'rent', en: 'Rent', tl: 'Rent/upahan', ceb: 'Rent/abang' },
        { value: 'groceries', en: 'Groceries', tl: 'Grocery/pagkain', ceb: 'Grocery/pagkaon' },
        { value: 'family_support', en: 'Family support', tl: 'Suporta sa pamilya', ceb: 'Suporta sa pamilya' },
        { value: 'tuition', en: 'Tuition/school expenses', tl: 'Tuition/school expenses', ceb: 'Tuition/school expenses' },
        { value: 'medicine', en: 'Medicine/health expenses', tl: 'Gamot/health expenses', ceb: 'Tambal/health expenses' },
        { value: 'debt', en: 'Debt/payment obligations', tl: 'Utang/payment obligations', ceb: 'Utang/payment obligations' },
        { value: 'church_giving', en: 'Church/tithes/giving', tl: 'Church/tithes/giving', ceb: 'Church/tithes/giving' },
        { value: 'savings', en: 'Savings', tl: 'Savings', ceb: 'Savings' },
        { value: 'business_capital', en: 'Business capital', tl: 'Puhunan sa negosyo', ceb: 'Puhunan sa negosyo' },
        { value: 'personal_goals', en: 'Personal goals', tl: 'Personal goals', ceb: 'Personal goals' },
    ],
    q6: [
        { value: 'clear_formula', en: 'Yes, with a clear formula', tl: 'Oo, may malinaw na formula', ceb: 'Oo, naay klaro nga formula' },
        { value: 'manual', en: 'Yes, but manually', tl: 'Oo, pero manual lang', ceb: 'Oo, pero manual ra' },
        { value: 'sometimes', en: 'Sometimes', tl: 'Minsan', ceb: 'Usahay' },
        { value: 'want_to', en: 'No, but I want to', tl: 'Hindi pa, pero gusto ko', ceb: 'Wala pa, pero gusto ko' },
        { value: 'not_needed', en: 'No, I do not need this', tl: 'Hindi, hindi ko kailangan', ceb: 'Dili, dili nako kinahanglan' },
    ],
    q7: [
        { value: 'unexpected_family', en: 'Unexpected family needs', tl: 'Biglaang pangangailangan ng pamilya', ceb: 'Kalit nga panginahanglan sa pamilya' },
        { value: 'impulse_spending', en: 'Impulse spending', tl: 'Impulse spending', ceb: 'Impulse spending' },
        { value: 'debt', en: 'Debt', tl: 'Utang', ceb: 'Utang' },
        { value: 'low_income', en: 'Low income', tl: 'Mababa ang income', ceb: 'Gamay ang income' },
        { value: 'irregular_income', en: 'Irregular income', tl: 'Hindi regular ang income', ceb: 'Dili regular ang income' },
        { value: 'too_many_bills', en: 'Too many bills', tl: 'Sobrang daming bills', ceb: 'Daghan kaayo ug bills' },
        { value: 'forgetting_transfers', en: 'Forgetting transfers', tl: 'Nakakalimutang mag-transfer', ceb: 'Makalimot ug transfer' },
        { value: 'no_clear_system', en: 'No clear system', tl: 'Walang malinaw na system', ceb: 'Walay klaro nga system' },
    ],
    q8: [
        { value: 'food_delivery', en: 'Food delivery/eating out', tl: 'Food delivery/kain sa labas', ceb: 'Food delivery/kaon sa gawas' },
        { value: 'shopping', en: 'Shopping', tl: 'Shopping', ceb: 'Shopping' },
        { value: 'online_purchases', en: 'Online purchases', tl: 'Online purchases', ceb: 'Online purchases' },
        { value: 'games_subscriptions', en: 'Games/apps/subscriptions', tl: 'Games/apps/subscriptions', ceb: 'Games/apps/subscriptions' },
        { value: 'nightlife', en: 'Nightlife/drinking', tl: 'Nightlife/inom', ceb: 'Nightlife/inom' },
        { value: 'smoking_vaping', en: 'Smoking/vaping', tl: 'Smoking/vaping', ceb: 'Smoking/vaping' },
        { value: 'gambling', en: 'Gambling/betting', tl: 'Gambling/betting', ceb: 'Gambling/betting' },
        { value: 'lending', en: 'Lending money', tl: 'Pagpapautang', ceb: 'Pagpahulam ug kwarta' },
        { value: 'none', en: 'None', tl: 'Wala', ceb: 'Wala' },
        { value: 'prefer_not_to_say', en: 'Prefer not to say', tl: 'Prefer not to say', ceb: 'Prefer not to say' },
    ],
    q9: [
        { value: 'split_income', en: 'Split income automatically', tl: 'Awtomatikong hatiin ang income', ceb: 'Automatic nga pagbahin sa income' },
        { value: 'track_transfers', en: 'Track actual transfers', tl: 'I-track ang actual transfers', ceb: 'I-track ang actual transfers' },
        { value: 'remind_unpaid', en: 'Remind me what is unpaid or unmoved', tl: 'I-remind ako kung ano ang unpaid o hindi pa naililipat', ceb: 'I-remind ko kung unsa ang unpaid o wala pa natransfer' },
        { value: 'protect_privacy', en: 'Protect my money privacy', tl: 'Protektahan ang money privacy ko', ceb: 'Protektahan akong money privacy' },
        { value: 'payday_discipline', en: 'Build payday discipline', tl: 'Tulungan akong maging disciplined tuwing payday', ceb: 'Tabangan ko mahimong disciplined kada payday' },
        { value: 'family_obligations', en: 'Manage family obligations', tl: 'I-manage ang family obligations', ceb: 'I-manage ang family obligations' },
        { value: 'plan_giving', en: 'Plan giving/tithes', tl: 'Planuhin ang giving/tithes', ceb: 'Planohon ang giving/tithes' },
        { value: 'save_goals', en: 'Save for goals', tl: 'Mag-save para sa goals', ceb: 'Mag-save para sa goals' },
    ],
    q10: [
        { value: 'early_access', en: 'Yes, I want early access', tl: 'Oo, gusto ko ng early access', ceb: 'Oo, gusto ko ug early access' },
        { value: 'beta_tester', en: 'Yes, I want to be a beta tester', tl: 'Oo, gusto kong maging beta tester', ceb: 'Oo, gusto ko mahimong beta tester' },
        { value: 'see_features', en: 'Maybe, I want to see the features first', tl: 'Siguro, gusto ko munang makita ang features', ceb: 'Siguro, gusto sa nako makita ang features' },
        { value: 'bank_support', en: 'Maybe, if it supports my bank/e-wallet', tl: 'Siguro, kung supported ang bank/e-wallet ko', ceb: 'Siguro, kung supported akong bank/e-wallet' },
        { value: 'not_interested', en: 'No, not interested', tl: 'Hindi, hindi ako interested', ceb: 'Dili, dili ko interested' },
    ],
} as const;

const QUESTION_PROMPTS: Record<
    keyof typeof QUESTION_OPTIONS,
    { en: string; tl: string; ceb: string; type: 'single' | 'multi'; skipNote?: { en: string; tl: string; ceb: string } }
> = {
    q1: {
        en: 'What best describes you?',
        tl: 'Ano ang pinakamahusay na naglalarawan sa iyo?',
        ceb: 'Unsa ang labing maayo nga naghulagway kanimo?',
        type: 'single',
    },
    q2: {
        en: 'What is your current life situation?',
        tl: 'Ano ang kasalukuyang sitwasyon mo sa buhay?',
        ceb: 'Unsa ang imong kahimtang karon?',
        type: 'single',
    },
    q3: {
        en: 'How many people depend on your income?',
        tl: 'Ilang tao ang umaasa sa income mo?',
        ceb: 'Pila ka tawo ang nagsalig sa imong income?',
        type: 'single',
    },
    q4: {
        en: 'When income arrives, what usually happens first?',
        tl: 'Kapag dumating ang income, ano ang karaniwang unang nangyayari?',
        ceb: 'Kon moabot ang income, unsa usually ang unang mahitabo?',
        type: 'single',
    },
    q5: {
        en: 'Which money responsibilities do you regularly handle?',
        tl: 'Anong money responsibilities ang regular mong hinahawakan?',
        ceb: 'Unsang money responsibilities ang regular nimo gihimo?',
        type: 'multi',
    },
    q6: {
        en: 'Do you currently split your income into fund buckets?',
        tl: 'Hinahati mo ba ang income mo sa fund buckets ngayon?',
        ceb: 'Gibahin ba nimo ang imong income sa fund buckets karon?',
        type: 'single',
    },
    q7: {
        en: 'What usually makes it hard to follow your plan?',
        tl: 'Ano ang kadalasang nagpapahirap sa pagsunod sa plano mo?',
        ceb: 'Unsa usually ang nagpalisod sa pagsunod sa imong plano?',
        type: 'single',
    },
    q8: {
        en: 'Are there spending habits you want better control over?',
        tl: 'May spending habits ka bang gusto mong mas kontrolado?',
        ceb: 'Naay spending habits ka nga gusto nimo mas kontrolado?',
        type: 'multi',
        skipNote: {
            en: 'You can skip anything that feels too personal.',
            tl: 'Pwede mong laktawan kung masyadong personal.',
            ceb: 'Pwede nimo laktawan kung personal ra kaayo.',
        },
    },
    q9: {
        en: 'What do you most want Kinsenas to help with?',
        tl: 'Ano ang pinaka-gusto mong matulungan ng Kinsenas?',
        ceb: 'Unsa ang pinaka-gusto nimo tabangan sa Kinsenas?',
        type: 'single',
    },
    q10: {
        en: 'Would you try a private payday planning app built for Filipinos?',
        tl: 'Susubukan mo ba ang private payday planning app na ginawa para sa mga Pilipino?',
        ceb: 'Sulayan ba nimo ang private payday planning app nga gihimo para sa mga Pilipino?',
        type: 'single',
    },
};

function buildQuestions(language: SurveyLanguage): SurveyQuestion[] {
    const langKey = language === 'tl' ? 'tl' : language === 'ceb' ? 'ceb' : 'en';

    return (Object.keys(QUESTION_OPTIONS) as Array<keyof typeof QUESTION_OPTIONS>).map((id) => {
        const meta = QUESTION_PROMPTS[id];
        const options = QUESTION_OPTIONS[id].map((option) => ({
            value: option.value,
            label: option[langKey],
        }));

        return {
            id,
            type: meta.type,
            prompt: meta[langKey],
            skipNote: meta.skipNote?.[langKey],
            options,
        };
    });
}

const RESULTS: Record<ResultSlug, { en: SurveyLanguageContent['results'][ResultSlug]; tl: SurveyLanguageContent['results'][ResultSlug]; ceb: SurveyLanguageContent['results'][ResultSlug] }> = {
    'family-first-planner': {
        en: {
            title: 'Family-First Planner',
            description:
                'Your income planning is not just about you. Kinsenas could help you organize support, bills, savings, and transfers in one private payday plan.',
        },
        tl: {
            title: 'Family-First Planner',
            description:
                'Hindi lang sarili ang iniisip sa pagplano ng income mo. Maaaring tulungan ka ng Kinsenas na ayusin ang suporta, bills, savings, at transfers sa isang private payday plan.',
        },
        ceb: {
            title: 'Family-First Planner',
            description:
                'Dili lang kaugalingon ang imong income planning. Makatabang ang Kinsenas nga i-organize ang suporta, bills, savings, ug transfers sa usa ka private payday plan.',
        },
    },
    'faith-giving-planner': {
        en: {
            title: 'Faith & Giving Planner',
            description:
                'Giving is part of your money rhythm. Kinsenas could help you plan tithes, giving, savings, and needs before spending takes over.',
        },
        tl: {
            title: 'Faith & Giving Planner',
            description:
                'Bahagi ng money rhythm mo ang pagbibigay. Maaaring tulungan ka ng Kinsenas na planuhin ang tithes, giving, savings, at needs bago maubos ng gastos.',
        },
        ceb: {
            title: 'Faith & Giving Planner',
            description:
                'Bahagi sa imong money rhythm ang paghatag. Makatabang ang Kinsenas nga planohon ang tithes, giving, savings, ug needs before ma-overtake sa gasto.',
        },
    },
    'bills-debt-organizer': {
        en: {
            title: 'Bills & Debt Organizer',
            description:
                'Your payday has important obligations. Kinsenas could help you see what needs to be paid, what was already moved, and what still remains.',
        },
        tl: {
            title: 'Bills & Debt Organizer',
            description:
                'May mahalagang obligasyon ang payday mo. Maaaring tulungan ka ng Kinsenas na makita kung ano ang kailangang bayaran, ano ang nailipat na, at ano ang natitira.',
        },
        ceb: {
            title: 'Bills & Debt Organizer',
            description:
                'Naay importante nga obligasyon ang imong payday. Makatabang ang Kinsenas nga makita unsa ang bayaran, unsa ang nailipat na, ug unsa ang nahabilin.',
        },
    },
    'goal-builder': {
        en: {
            title: 'Goal Builder',
            description:
                'You want your income to move toward something meaningful. Kinsenas could help you split payday into savings, goals, and real transfer actions.',
        },
        tl: {
            title: 'Goal Builder',
            description:
                'Gusto mong pumunta ang income mo sa may saysay. Maaaring tulungan ka ng Kinsenas na hatiin ang payday sa savings, goals, at tunay na transfer actions.',
        },
        ceb: {
            title: 'Goal Builder',
            description:
                'Gusto nimo ang imong income mopadulong sa may kahulogan. Makatabang ang Kinsenas nga bahinon ang payday sa savings, goals, ug tinuod nga transfer actions.',
        },
    },
    'transfer-tracker': {
        en: {
            title: 'Transfer Tracker',
            description:
                'Your challenge is not only planning. It is knowing whether the money was actually moved. Kinsenas is being built for that gap.',
        },
        tl: {
            title: 'Transfer Tracker',
            description:
                'Hindi lang pagplano ang hamon mo. Alamin kung nailipat ba talaga ang pera. Para diyan binubuo ang Kinsenas.',
        },
        ceb: {
            title: 'Transfer Tracker',
            description:
                'Dili lang planning ang imong challenge. Ang mahibalo kung natransfer ba gyud ang kwarta. Para ana gitukod ang Kinsenas.',
        },
    },
    'discipline-builder': {
        en: {
            title: 'Discipline Builder',
            description:
                'You want a clearer routine after payday. Kinsenas could help you decide where income should go before it disappears.',
        },
        tl: {
            title: 'Discipline Builder',
            description:
                'Gusto mo ng mas malinaw na routine pagkatapos ng payday. Maaaring tulungan ka ng Kinsenas na magdesisyon kung saan dapat pumunta ang income bago mawala.',
        },
        ceb: {
            title: 'Discipline Builder',
            description:
                'Gusto nimo og mas klaro nga routine after payday. Makatabang ang Kinsenas nga magdesisyon asa dapat padulong ang income before mawala.',
        },
    },
    'payday-planner': {
        en: {
            title: 'Payday Planner',
            description:
                'Your answers show that payday needs a clearer system. Kinsenas could help turn income into a private plan you can actually follow.',
        },
        tl: {
            title: 'Payday Planner',
            description:
                'Ipinapakita ng sagot mo na kailangan ng payday ng mas malinaw na system. Maaaring tulungan ka ng Kinsenas na gawing private plan ang income na masusunod mo.',
        },
        ceb: {
            title: 'Payday Planner',
            description:
                'Nagpakita ang imong tubag nga nanginahanglan ang payday og mas klaro nga system. Makatabang ang Kinsenas nga himoon ang income nga private plan nga masunod nimo.',
        },
    },
};

const SHARED: Record<
    SurveyLanguage,
    Omit<SurveyLanguageContent, 'questions' | 'results' | 'languageLabel'>
> = {
    en: {
        intro: 'Answer a few questions and see what kind of payday plan fits your life.',
        privacyNote: 'Your answers are only used to shape your Kinsenas preview.',
        progressLabel: (current, total) => `Question ${current} of ${total}`,
        back: 'Back',
        continue: 'Continue',
        multiSelectHint: 'Select all that apply.',
        resultPreviewLabel: 'Your payday preview',
        thankYouTitle: 'Thank you',
        interstitials: {
            afterQ3: 'Payday is rarely just about one person. For many Filipinos, income already has places to go before it even arrives.',
            afterQ6: 'Most budgeting apps focus on spending. Kinsenas is more focused on what happens right after income arrives.',
        },
        loadingSteps: [
            'Mapping your income responsibilities...',
            'Checking your payday habits...',
            'Building your Kinsenas payday preview...',
        ],
        loadingTitle: 'Building your plan',
        loadingSubtitle: 'Just a moment...',
        resultCTA: {
            headline: 'Want your Kinsenas payday plan when early access opens?',
            emailLabel: 'Email',
            nameLabel: 'Name (optional)',
            namePlaceholder: 'Your name',
            submit: 'Join Early Access',
            emailRequired: 'Please enter your email.',
            emailInvalid: 'Please enter a valid email address.',
            submitError: 'Something went wrong. Please try again.',
        },
        thankYou: 'Thank you. Your answers help shape Kinsenas around real Filipino payday habits.',
    },
    tl: {
        intro: 'Sagutin ang ilang tanong at tingnan kung anong payday plan ang bagay sa buhay mo.',
        privacyNote: 'Gagamitin lang ang sagot mo para mabuo ang Kinsenas preview mo.',
        progressLabel: (current, total) => `Tanong ${current} ng ${total}`,
        back: 'Bumalik',
        continue: 'Magpatuloy',
        multiSelectHint: 'Piliin ang lahat ng naaangkop.',
        resultPreviewLabel: 'Ang payday preview mo',
        thankYouTitle: 'Salamat',
        interstitials: {
            afterQ3: 'Hindi lang sarili ang iniisip kapag payday. Para sa maraming Pilipino, may pupuntahan na agad ang income bago pa ito dumating.',
            afterQ6: 'Karamihan ng budgeting apps, spending ang focus. Ang Kinsenas, mas naka-focus sa nangyayari pagkatapos dumating ang income.',
        },
        loadingSteps: [
            'Inaayos ang income responsibilities mo...',
            'Tinitingnan ang payday habits mo...',
            'Binubuo ang Kinsenas payday preview mo...',
        ],
        loadingTitle: 'Binubuo ang plano mo',
        loadingSubtitle: 'Sandali lang...',
        resultCTA: {
            headline: 'Gusto mo bang makuha ang Kinsenas payday plan mo kapag bukas na ang early access?',
            emailLabel: 'Email',
            nameLabel: 'Pangalan (optional)',
            namePlaceholder: 'Pangalan mo',
            submit: 'Sumali sa Early Access',
            emailRequired: 'Ilagay ang email mo.',
            emailInvalid: 'Maglagay ng valid na email address.',
            submitError: 'May nangyaring mali. Subukan ulit.',
        },
        thankYou: 'Salamat. Makakatulong ang sagot mo para mabuo ang Kinsenas base sa totoong payday habits ng mga Pilipino.',
    },
    ceb: {
        intro: 'Tubaga ang pipila ka pangutana ug tan-awa unsang payday plan ang bagay sa imong kinabuhi.',
        privacyNote: 'Ang imong tubag gamiton ra para mahimo ang imong Kinsenas preview.',
        progressLabel: (current, total) => `Pangutana ${current} sa ${total}`,
        back: 'Balik',
        continue: 'Padayon',
        multiSelectHint: 'Pilia ang tanan nga angay.',
        resultPreviewLabel: 'Imong payday preview',
        thankYouTitle: 'Salamat',
        interstitials: {
            afterQ3: 'Dili lang kaugalingon ang gihunahuna kung payday. Para sa daghang Pilipino, naa nay padulngan ang income bisan wala pa moabot.',
            afterQ6: 'Kasagaran budgeting apps, spending ang focus. Ang Kinsenas, mas naka-focus sa mahitabo pagkahuman moabot ang income.',
        },
        loadingSteps: [
            'Gi-map ang imong income responsibilities...',
            'Gi-check ang imong payday habits...',
            'Gihimo ang imong Kinsenas payday preview...',
        ],
        loadingTitle: 'Gihimo ang imong plano',
        loadingSubtitle: 'Hinay lang...',
        resultCTA: {
            headline: 'Gusto nimo makuha ang imong Kinsenas payday plan kung abli na ang early access?',
            emailLabel: 'Email',
            nameLabel: 'Ngalan (optional)',
            namePlaceholder: 'Imong ngalan',
            submit: 'Apil sa Early Access',
            emailRequired: 'Ibutang ang imong email.',
            emailInvalid: 'Ibutang og valid nga email address.',
            submitError: 'Naay sayop. Sulayi pag-usab.',
        },
        thankYou: 'Salamat. Makatabang imong tubag para mahulma ang Kinsenas base sa tinuod nga payday habits sa mga Pilipino.',
    },
};

const LANGUAGE_LABELS: Record<SurveyLanguage, string> = {
    en: 'English',
    tl: 'Tagalog',
    ceb: 'Bisaya',
};

export const SURVEY_LANGUAGES: SurveyLanguage[] = ['en', 'tl', 'ceb'];

export function getSurveyContent(language: SurveyLanguage): SurveyLanguageContent {
    const langKey = language;

    const results = Object.fromEntries(
        (Object.keys(RESULTS) as ResultSlug[]).map((slug) => [slug, RESULTS[slug][langKey]]),
    ) as Record<ResultSlug, SurveyLanguageContent['results'][ResultSlug]>;

    return {
        languageLabel: LANGUAGE_LABELS[language],
        ...SHARED[language],
        questions: buildQuestions(language),
        results,
    };
}

export function getLanguageDisplayName(language: SurveyLanguage): string {
    return LANGUAGE_LABELS[language];
}

export const RESULT_ALLOCATION_INDEX: Record<ResultSlug, 1 | 2 | 3 | 4 | 5 | 6> = {
    'family-first-planner': 4,
    'faith-giving-planner': 3,
    'bills-debt-organizer': 1,
    'goal-builder': 2,
    'transfer-tracker': 5,
    'discipline-builder': 6,
    'payday-planner': 1,
};
