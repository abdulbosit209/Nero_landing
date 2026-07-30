<?php

declare(strict_types=1);

/**
 * "Pricing" section: one card per service (LandingContent::SERVICES) showing its
 * starting price from params['pricing'] and a CTA that jumps to the lead form.
 *
 * @var yii\web\View $this
 */

use app\models\LandingContent;
use yii\helpers\Html;
use yii\helpers\Url;

// Starting price per service slug, in UZS — edit config/params.php (or params-local.php) to change.
$pricing = Yii::$app->params['pricing'] ?? [];
?>
<section id="pricing" class="nero-section nero-section-surface">
    <div class="nero-section-head">
        <div class="nero-eyebrow"><?= Html::encode(Yii::t('app', 'pricing.sectionEyebrow')) ?></div>
        <h2 class="nero-heading"><?= Html::encode(Yii::t('app', 'pricing.sectionTitle')) ?></h2>
        <p class="nero-sub"><?= Html::encode(Yii::t('app', 'pricing.sectionSub')) ?></p>
    </div>

    <div class="nero-pricing-grid">
        <?php foreach (LandingContent::SERVICES as $slug): ?>
            <div class="nero-pricing-card">
                <div>
                    <div class="nero-pricing-title"><?= Html::encode(Yii::t('app', "services.$slug.title")) ?></div>
                    <div class="nero-pricing-desc"><?= Html::encode(Yii::t('app', "services.$slug.desc")) ?></div>
                </div>
                <div class="nero-pricing-amount-row">
                    <span class="nero-pricing-from"><?= Html::encode(Yii::t('app', 'pricing.fromLabel')) ?></span>
                    <span class="nero-pricing-amount"><?= number_format((float) ($pricing[$slug] ?? 0), 0, '.', ' ') ?></span>
                    <span class="nero-pricing-unit"><?= Html::encode(Yii::t('app', 'pricing.unit')) ?></span>
                </div>
                <a href="<?= Url::to(['/site/index', '#' => 'contact']) ?>" class="nero-pricing-cta">
                    <?= Html::encode(Yii::t('app', 'pricing.cta')) ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>
