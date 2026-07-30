<?php

declare(strict_types=1);

/**
 * "Recent work" gallery: one card per LandingContent::WORKS entry. The photo tile is
 * currently a striped placeholder (see .nero-work-photo in web/css/site.css) labeled
 * with `works.$slug.photoLabel` — swap in a real <img> once project photos exist.
 *
 * @var yii\web\View $this
 */

use app\models\LandingContent;
use yii\helpers\Html;
?>
<section id="works" class="nero-section">
    <div class="nero-section-head">
        <div class="nero-eyebrow"><?= Html::encode(Yii::t('app', 'works.sectionEyebrow')) ?></div>
        <h2 class="nero-heading"><?= Html::encode(Yii::t('app', 'works.sectionTitle')) ?></h2>
        <p class="nero-sub"><?= Html::encode(Yii::t('app', 'works.sectionSub')) ?></p>
    </div>

    <div class="nero-works-grid">
        <?php foreach (LandingContent::WORKS as $slug): ?>
            <div>
                <div class="nero-work-photo">
                    <span class="nero-work-photo-label">
                        <?= Html::encode(Yii::t('app', "works.$slug.photoLabel")) ?>
                    </span>
                </div>
                <div class="nero-work-tag"><?= Html::encode(Yii::t('app', "works.$slug.tag")) ?></div>
                <div class="nero-work-title"><?= Html::encode(Yii::t('app', "works.$slug.title")) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
