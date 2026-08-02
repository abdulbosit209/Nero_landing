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

    // starting prices per service category, in UZS (edit with real prices).
    // A value may be a single number, or an array of 'bodyType' => price when the
    // price varies by car body type (see pricing.bodyType.* in messages/{lang}/app.php
    // for the labels shown for each bodyType key) — views/site/_pricing.php renders
    // either shape.
    'pricing' => [
        'dent' => 200000,
        'paint' => 500000,
        'polish' => [
            'sedan' => 500000,
            'crossover' => 700000,
        ],
        'ceramic' => 3500000,
        'glass' => 120000,
    ],
];

// Container/PaaS deploys (see Dockerfile, render.yaml) have no params-local.php — they
// supply secrets as environment variables instead.
$envOverrides = array_filter([
    'cookieValidationKey' => getenv('COOKIE_VALIDATION_KEY') ?: null,
    'telegramBotToken' => getenv('TELEGRAM_BOT_TOKEN') ?: null,
    'telegramChatId' => getenv('TELEGRAM_CHAT_ID') ?: null,
], static fn ($value) => $value !== null);
$params = array_merge($params, $envOverrides);

// Local dev override file takes precedence over both the defaults above and env vars.
$localParamsFile = __DIR__ . '/params-local.php';
if (is_file($localParamsFile)) {
    $params = array_merge($params, require $localParamsFile);
}

return $params;
