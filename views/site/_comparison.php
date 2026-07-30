<?php

declare(strict_types=1);

/**
 * "Nero vs. an average shop" comparison table: one row per LandingContent::COMPARISON_ROWS.
 * Nero's column is always a checkmark; the "others" column comes from
 * `compare.$row.others` (free text, since a typical shop's answer varies).
 *
 * @var yii\web\View $this
 */

use app\models\LandingContent;
use yii\helpers\Html;
?>
<section id="compare" class="nero-section nero-section-tight">
    <div class="nero-section-head">
        <div class="nero-eyebrow"><?= Html::encode(Yii::t('app', 'compare.sectionEyebrow')) ?></div>
        <h2 class="nero-heading"><?= Html::encode(Yii::t('app', 'compare.sectionTitle')) ?></h2>
        <p class="nero-sub"><?= Html::encode(Yii::t('app', 'compare.sectionSub')) ?></p>
    </div>

    <div class="nero-compare-wrap">
        <div class="compare-row nero-compare-head">
            <div></div>
            <div class="nero-compare-brand">NERO</div>
            <div class="nero-compare-col-label"><?= Html::encode(Yii::t('app', 'compare.colOthers')) ?></div>
        </div>
        <?php foreach (LandingContent::COMPARISON_ROWS as $row): ?>
            <div class="compare-row">
                <div class="compare-cell nero-compare-feature">
                    <?= Html::encode(Yii::t('app', "compare.$row.feature")) ?>
                </div>
                <div class="nero-compare-check">&#10003;</div>
                <div class="compare-cell nero-compare-other">
                    <?= Html::encode(Yii::t('app', "compare.$row.others")) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
