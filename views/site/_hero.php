<?php

declare(strict_types=1);

/**
 * Landing page hero: headline, sub-copy, primary CTAs and the trust-stat row.
 * Rendered first inside views/site/index.php.
 *
 * @var yii\web\View $this
 */

use yii\helpers\Html;
use yii\helpers\Url;

$phoneNumbers = Yii::$app->params['phoneNumbers'] ?? [];
// tel: links must contain only digits and a leading "+" — strip spaces/formatting from the display number.
// The CTA links to the first listed number.
$phoneHref = 'tel:' . preg_replace('/[^+\d]/', '', $phoneNumbers[0] ?? '');
?>
<div id="hero">
    <div class="nero-hero-inner">
        <div class="nero-badge-pill">
            <span class="nero-badge-dot"></span>
            <?= Html::encode(Yii::t('app', 'topBadge')) ?>
        </div>
        <h1 id="hero-title"><?= Html::encode(Yii::t('app', 'hero.title')) ?></h1>
        <p class="nero-hero-sub"><?= Html::encode(Yii::t('app', 'hero.sub')) ?></p>
        <div class="nero-hero-ctas">
            <a href="<?= Url::to(['/site/index', '#' => 'contact']) ?>" class="nero-btn nero-btn-lg">
                <?= Html::encode(Yii::t('app', 'ctaRequest')) ?>
            </a>
            <a href="<?= Html::encode($phoneHref) ?>" class="nero-btn nero-btn-lg nero-btn-outline">
                <?= Html::encode(Yii::t('app', 'ctaCall')) ?>
            </a>
        </div>
        <div id="hero-stats">
            <div>
                <div class="nero-stat-num">12 <?= Html::encode(Yii::t('app', 'stat.moShort')) ?></div>
                <div class="nero-stat-label"><?= Html::encode(Yii::t('app', 'stat.warrantyLabel')) ?></div>
            </div>
            <div>
                <div class="nero-stat-num">100%</div>
                <div class="nero-stat-label"><?= Html::encode(Yii::t('app', 'stat.certifiedLabel')) ?></div>
            </div>
        </div>
    </div>
</div>
