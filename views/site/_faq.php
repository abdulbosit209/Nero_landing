<?php

declare(strict_types=1);

/**
 * FAQ accordion: one <details> per LandingContent::FAQ_IDS entry. All items share
 * `name="faq-group"` so the browser's native <details> behavior keeps only one open
 * at a time — no JS needed.
 *
 * @var yii\web\View $this
 */

use app\models\LandingContent;
use yii\helpers\Html;
?>
<section id="faq" class="nero-section nero-section-narrow nero-section-surface">
    <div class="nero-section-head" style="margin-bottom:56px;">
        <div class="nero-eyebrow"><?= Html::encode(Yii::t('app', 'faq.sectionEyebrow')) ?></div>
        <h2 class="nero-heading" style="margin:0;"><?= Html::encode(Yii::t('app', 'faq.sectionTitle')) ?></h2>
    </div>

    <div class="nero-faq-list">
        <?php // The first FAQ entry starts expanded so the section isn't empty-looking on load. ?>
        <?php foreach (LandingContent::FAQ_IDS as $i => $id): ?>
            <details class="nero-faq-item" name="faq-group" <?= $i === 0 ? 'open' : '' ?>>
                <summary>
                    <?= Html::encode(Yii::t('app', "faq.$id.q")) ?>
                    <span class="nero-faq-marker">
                        <span class="nero-faq-marker-plus">+</span>
                        <span class="nero-faq-marker-minus">&minus;</span>
                    </span>
                </summary>
                <div class="nero-faq-body">
                    <?= Html::encode(Yii::t('app', "faq.$id.a")) ?>
                </div>
            </details>
        <?php endforeach; ?>
    </div>
</section>
