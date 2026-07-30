<?php

$params = [
    // Secret key used by Yii to validate cookies/CSRF tokens — must be a long random
    // string and must never be committed. The real value belongs in the gitignored
    // config/params-local.php (copy config/params-local.php.example to create it, or
    // generate one with: php -r "echo bin2hex(random_bytes(32)), PHP_EOL;").
    'cookieValidationKey' => '',

    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',

    'brandName' => 'NERO',
    'defaultLanguage' => 'ru',
    // Language codes the site is translated into — see messages/{code}/app.php.
    // LanguageSelector (components/LanguageSelector.php) reads this to decide which
    // `?lang=` values are valid; the header's language switcher reads languageLabels
    // below for the display label of each code. To add a language: add its code here,
    // add a label below, and create messages/{code}/app.php with the same keys as
    // messages/en/app.php.
    'supportedLanguages' => ['en', 'ru', 'uz'],
    'languageLabels' => ['en' => 'EN', 'ru' => 'RU', 'uz' => 'UZ'],

    'telegramBotToken' => '',
    'telegramChatId' => '',

    // contact details shown in the header, footer and contact section
    'phoneNumber' => '+998 90 000 00 00',
    'address' => 'Tashkent, Uzbekistan',

    // starting prices per service category, in UZS (edit with real prices)
    'pricing' => [
        'dent' => 150000,
        'paint' => 100000,
        'polish' => 300000,
        'glass' => 120000,
    ],
];

$localParamsFile = __DIR__ . '/params-local.php';
if (is_file($localParamsFile)) {
    $params = array_merge($params, require $localParamsFile);
}

return $params;
