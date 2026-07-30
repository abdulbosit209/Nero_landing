<?php

declare(strict_types=1);

namespace app\components;

use yii\base\BootstrapInterface;
use yii\web\Application;
use yii\web\Cookie;

/**
 * Resolves the active application language from the `?lang=` query parameter
 * (persisting the choice in a cookie) or, failing that, from a previously set cookie.
 */
class LanguageSelector implements BootstrapInterface
{
    public const COOKIE_NAME = 'lang';

    public function bootstrap($app): void
    {
        if (!$app instanceof Application) {
            return;
        }

        /** @var string[] $supported */
        $supported = $app->params['supportedLanguages'] ?? ['en', 'ru', 'uz'];

        $requested = $app->request->get('lang');
        if (is_string($requested) && in_array($requested, $supported, true)) {
            $app->language = $requested;
            $app->response->cookies->add(new Cookie([
                'name' => self::COOKIE_NAME,
                'value' => $requested,
                'expire' => time() + 60 * 60 * 24 * 365,
            ]));

            return;
        }

        $cookieLang = $app->request->cookies->getValue(self::COOKIE_NAME);
        if (is_string($cookieLang) && in_array($cookieLang, $supported, true)) {
            $app->language = $cookieLang;
        }
    }
}
