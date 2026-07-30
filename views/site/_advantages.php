<?php

declare(strict_types=1);

/**
 * "Why choose us" section: one card per LandingContent::ADVANTAGES, each with an
 * icon from LandingContent::ADVANTAGE_ICONS.
 *
 * @var yii\web\View $this
 */

use app\models\LandingContent;
use yii\helpers\Html;
?>
<section id="advantages" class="nero-section nero-section-surface">
    <div class="nero-section-head">
        <div class="nero-eyebrow"><?= Html::encode(Yii::t('app', 'advantages.sectionEyebrow')) ?></div>
        <h2 class="nero-heading"><?= Html::encode(Yii::t('app', 'advantages.sectionTitle')) ?></h2>
        <p class="nero-sub"><?= Html::encode(Yii::t('app', 'advantages.sectionSub')) ?></p>
    </div>

    <div class="nero-advantages-grid">
        <?php foreach (LandingContent::ADVANTAGES as $slug): ?>
            <div class="nero-advantage-card">
                <div class="nero-advantage-icon" aria-hidden="true">
                    <?php // Icon markup is trusted, hardcoded SVG from LandingContent — not user input, safe to echo raw. ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?= LandingContent::ADVANTAGE_ICONS[$slug] ?></svg>
                </div>
                <h3 class="nero-advantage-title"><?= Html::encode(Yii::t('app', "advantages.$slug.title")) ?></h3>
                <p class="nero-advantage-desc"><?= Html::encode(Yii::t('app', "advantages.$slug.desc")) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>
