<?php

declare(strict_types=1);

/**
 * Site header: top utility bar, logo, in-page section nav, language switcher and the
 * primary CTA button. Shared by every page via views/layouts/main.php.
 *
 * @var yii\web\View $this
 */

use yii\helpers\Html;
use yii\helpers\Url;

// anchor id (must match a <section id="..."> on the landing page) => nav label.
$navItems = [
    'services' => Yii::t('app', 'nav.services'),
    'advantages' => Yii::t('app', 'nav.advantages'),
    'works' => Yii::t('app', 'nav.works'),
    'pricing' => Yii::t('app', 'nav.pricing'),
    'compare' => Yii::t('app', 'nav.compare'),
    'faq' => Yii::t('app', 'nav.faq'),
];

// Displayed labels for the language switcher, keyed by language code (see config/params.php).
$languageLabels = Yii::$app->params['languageLabels'] ?? [];
$phoneNumber = Yii::$app->params['phoneNumber'] ?? '';
$phoneHref = 'tel:' . preg_replace('/[^+\d]/', '', $phoneNumber);
?>
<div class="nero-header-wrap">
    <div id="topbar" class="nero-topbar">
        <span><?= Html::encode(Yii::t('app', 'topBadge')) ?></span>
        <div class="nero-topbar-utility">
            <a href="<?= Html::encode($phoneHref) ?>"><?= Html::encode($phoneNumber) ?></a>
        </div>
    </div>
    <div id="header-row">
        <a class="nero-logo" href="<?= Yii::$app->homeUrl ?>">
            <img src="<?= Yii::getAlias('@web/images/nero-icon.jpg') ?>" alt="<?= Html::encode(Yii::$app->params['brandName'] ?? 'NERO') ?>">
        </a>
        <nav id="main-nav">
            <?php foreach ($navItems as $anchor => $label): ?>
                <a href="<?= Url::to(['/site/index', '#' => $anchor]) ?>"><?= Html::encode($label) ?></a>
            <?php endforeach; ?>
        </nav>
        <div style="display:flex;align-items:center;gap:16px;">
            <?php // Navigating to ?lang=<code> makes components/LanguageSelector.php switch the app language and persist it in a cookie. ?>
            <select class="nero-lang-select" onchange="window.location.href=this.value" aria-label="Language">
                <?php foreach ($languageLabels as $code => $label): ?>
                    <option
                        value="<?= Html::encode(Url::to(['/site/index', 'lang' => $code])) ?>"
                        <?= Yii::$app->language === $code ? 'selected' : '' ?>
                    ><?= Html::encode($label) ?></option>
                <?php endforeach; ?>
            </select>
            <a href="<?= Url::to(['/site/index', '#' => 'contact']) ?>" class="nero-btn">
                <?= Html::encode(Yii::t('app', 'ctaRequest')) ?>
            </a>
        </div>
    </div>
</div>
