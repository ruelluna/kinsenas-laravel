<?php

use App\Enums\BankInstitutionType;

if (! function_exists('bankInstitutionFaviconUrl')) {
    function bankInstitutionFaviconUrl(string $domain): string
    {
        return 'https://t1.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https://'
            .urlencode($domain)
            .'&size=128';
    }
}

/**
 * @return list<array{slug: string, name: string, type: BankInstitutionType, logo_url: string}>
 */
return [
    [
        'slug' => 'bdo',
        'name' => 'BDO Unibank',
        'type' => BankInstitutionType::Bank,
        'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/4/49/BDO_Unibank_%28logo%29.svg',
    ],
    [
        'slug' => 'bpi',
        'name' => 'Bank of the Philippine Islands',
        'type' => BankInstitutionType::Bank,
        'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/1/1a/Official_BPI_Logo.svg',
    ],
    [
        'slug' => 'metrobank',
        'name' => 'Metrobank',
        'type' => BankInstitutionType::Bank,
        'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/e/e5/Metropolitan_Bank_and_Trust_Company.svg',
    ],
    [
        'slug' => 'landbank',
        'name' => 'Land Bank of the Philippines',
        'type' => BankInstitutionType::Bank,
        'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/8/83/Landbank.svg',
    ],
    [
        'slug' => 'pnb',
        'name' => 'Philippine National Bank',
        'type' => BankInstitutionType::Bank,
        'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/b/b0/Philippine-National-Bank-logo.svg',
    ],
    [
        'slug' => 'security-bank',
        'name' => 'Security Bank',
        'type' => BankInstitutionType::Bank,
        'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/5/5a/Security_Bank_logo.svg',
    ],
    [
        'slug' => 'unionbank',
        'name' => 'UnionBank of the Philippines',
        'type' => BankInstitutionType::Bank,
        'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/6/61/Unionbank_2018_logo.svg',
    ],
    [
        'slug' => 'chinabank',
        'name' => 'China Banking Corporation',
        'type' => BankInstitutionType::Bank,
        'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/f/fb/Chinabank_2024.svg',
    ],
    [
        'slug' => 'rcbc',
        'name' => 'RCBC',
        'type' => BankInstitutionType::Bank,
        'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/f/ff/RCBC_logo.svg',
    ],
    [
        'slug' => 'eastwest',
        'name' => 'EastWest Bank',
        'type' => BankInstitutionType::Bank,
        'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/d/d8/EastWest_Bank_2011_h-pos_logo.svg',
    ],
    [
        'slug' => 'dbp',
        'name' => 'Development Bank of the Philippines',
        'type' => BankInstitutionType::Bank,
        'logo_url' => bankInstitutionFaviconUrl('dbp.ph'),
    ],
    [
        'slug' => 'aub',
        'name' => 'Asia United Bank',
        'type' => BankInstitutionType::Bank,
        'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/9/9b/Asia_United_Bank_logo.svg',
    ],
    [
        'slug' => 'pbcom',
        'name' => 'Philippine Bank of Communications',
        'type' => BankInstitutionType::Bank,
        'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/6/6d/Philippines_Bank_of_Communications_Logo_%282017%29.svg',
    ],
    [
        'slug' => 'robinsons-bank',
        'name' => 'Robinsons Bank',
        'type' => BankInstitutionType::Bank,
        'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/7/78/Robinsons_Bank_logo.svg',
    ],
    [
        'slug' => 'maybank-ph',
        'name' => 'Maybank Philippines',
        'type' => BankInstitutionType::Bank,
        'logo_url' => bankInstitutionFaviconUrl('maybank.com.ph'),
    ],
    [
        'slug' => 'cimb-ph',
        'name' => 'CIMB Bank Philippines',
        'type' => BankInstitutionType::Bank,
        'logo_url' => bankInstitutionFaviconUrl('cimb.com.ph'),
    ],
    [
        'slug' => 'ctbc-ph',
        'name' => 'CTBC Bank (Philippines)',
        'type' => BankInstitutionType::Bank,
        'logo_url' => bankInstitutionFaviconUrl('ctbcbank.com.ph'),
    ],
    [
        'slug' => 'bank-of-commerce',
        'name' => 'Bank of Commerce',
        'type' => BankInstitutionType::Bank,
        'logo_url' => bankInstitutionFaviconUrl('bankcom.com.ph'),
    ],
    [
        'slug' => 'veterans-bank',
        'name' => 'Philippine Veterans Bank',
        'type' => BankInstitutionType::Bank,
        'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/1/13/Philippine_Veterans_Bank_logo.svg',
    ],
    [
        'slug' => 'gotyme',
        'name' => 'GoTyme Bank',
        'type' => BankInstitutionType::Bank,
        'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/d/d5/GoTyme_Bank_logo.svg',
    ],
    [
        'slug' => 'maya-bank',
        'name' => 'Maya Bank',
        'type' => BankInstitutionType::Bank,
        'logo_url' => bankInstitutionFaviconUrl('mayabank.ph'),
    ],
    [
        'slug' => 'tonik',
        'name' => 'Tonik Digital Bank',
        'type' => BankInstitutionType::Bank,
        'logo_url' => bankInstitutionFaviconUrl('tonikbank.com'),
    ],
    [
        'slug' => 'seabank',
        'name' => 'SeaBank Philippines',
        'type' => BankInstitutionType::Bank,
        'logo_url' => bankInstitutionFaviconUrl('seabank.ph'),
    ],
    [
        'slug' => 'uniondigital',
        'name' => 'UnionDigital Bank',
        'type' => BankInstitutionType::Bank,
        'logo_url' => bankInstitutionFaviconUrl('uniondigitalbank.io'),
    ],
    [
        'slug' => 'unobank',
        'name' => 'UNO Digital Bank',
        'type' => BankInstitutionType::Bank,
        'logo_url' => bankInstitutionFaviconUrl('unobank.com'),
    ],
    [
        'slug' => 'ofbank',
        'name' => 'Overseas Filipino Bank',
        'type' => BankInstitutionType::Bank,
        'logo_url' => bankInstitutionFaviconUrl('ofbank.gov.ph'),
    ],
    [
        'slug' => 'gcash',
        'name' => 'GCash',
        'type' => BankInstitutionType::EWallet,
        'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/5/52/GCash_logo.svg',
    ],
    [
        'slug' => 'maya',
        'name' => 'Maya',
        'type' => BankInstitutionType::EWallet,
        'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/e/e6/Maya_logo.svg',
    ],
    [
        'slug' => 'grabpay',
        'name' => 'GrabPay',
        'type' => BankInstitutionType::EWallet,
        'logo_url' => bankInstitutionFaviconUrl('grab.com'),
    ],
    [
        'slug' => 'shopeepay',
        'name' => 'ShopeePay',
        'type' => BankInstitutionType::EWallet,
        'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/0/0e/Shopee_logo.svg',
    ],
    [
        'slug' => 'coins-ph',
        'name' => 'Coins.ph',
        'type' => BankInstitutionType::EWallet,
        'logo_url' => bankInstitutionFaviconUrl('coins.ph'),
    ],
    [
        'slug' => 'palawan-pay',
        'name' => 'Palawan Pay',
        'type' => BankInstitutionType::EWallet,
        'logo_url' => bankInstitutionFaviconUrl('palawanpawnshop.com'),
    ],
    [
        'slug' => 'tayo-cash',
        'name' => 'TayoCash',
        'type' => BankInstitutionType::EWallet,
        'logo_url' => bankInstitutionFaviconUrl('tayocash.com'),
    ],
    [
        'slug' => 'zybi-pay',
        'name' => 'Zybi Pay',
        'type' => BankInstitutionType::EWallet,
        'logo_url' => bankInstitutionFaviconUrl('zybitech.com'),
    ],
];
