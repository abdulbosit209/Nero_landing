<?php

declare(strict_types=1);

/**
 * "Services" section: numbered grid of the service categories in LandingContent::SERVICES.
 *
 * @var yii\web\View $this
 */

use app\models\LandingContent;
use yii\helpers\Html;
?>
<section id="services" class="nero-section">
    <div class="nero-section-head">
        <div class="nero-eyebrow"><?= Html::encode(Yii::t('app', 'services.sectionEyebrow')) ?></div>
        <h2 class="nero-heading"><?= Html::encode(Yii::t('app', 'services.sectionTitle')) ?></h2>
        <p class="nero-sub"><?= Html::encode(Yii::t('app', 'services.sectionSub')) ?></p>
    </div>

    <div class="nero-services-grid">
        <?php foreach (LandingContent::SERVICES as $i => $slug): ?>
            <div class="nero-service-card">
                <div class="nero-service-num"><?= sprintf('%02d', $i + 1) ?></div>
                <h3 class="nero-service-title"><?= Html::encode(Yii::t('app', "services.$slug.title")) ?></h3>
                <p class="nero-service-desc"><?= Html::encode(Yii::t('app', "services.$slug.desc")) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>
