<?php

declare(strict_types=1);

/**
 * Landing page: composes the section partials in on-page order. Each partial owns its
 * own <section id="..."> anchor, which the header/footer nav links and pricing/hero
 * CTAs jump to via Url::to(['/site/index', '#' => '<anchor>']).
 *
 * @var yii\web\View $this
 * @var app\models\LeadForm $leadForm
 */

$this->title = Yii::t('app', 'meta.title');
$this->params['meta_description'] = Yii::t('app', 'meta.description');
?>
<div class="site-index">
    <?= $this->render('_hero') ?>
    <?= $this->render('_services') ?>
    <?= $this->render('_advantages') ?>
    <?= $this->render('_works') ?>
    <?= $this->render('_pricing') ?>
    <?= $this->render('_comparison') ?>
    <?= $this->render('_faq') ?>
    <?= $this->render('_lead-form', ['model' => $leadForm]) ?>
</div>
