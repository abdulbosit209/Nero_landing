<?php

declare(strict_types=1);

/**
 * Site footer: brand tagline, quick nav links and contact details. Shared by every
 * page via views/layouts/main.php.
 *
 * @var yii\web\View $this
 */

use yii\helpers\Html;
use yii\helpers\Url;

$phoneNumbers = Yii::$app->params['phoneNumbers'] ?? [];
$address = Yii::$app->params['address'] ?? '';
?>
<footer id="footer">
    <div id="footer-grid">
        <div>
            <a class="nero-logo" href="<?= Yii::$app->homeUrl ?>">
                <img src="<?= Yii::getAlias('@web/images/nero-icon.jpg') ?>" alt="<?= Html::encode(Yii::$app->params['brandName'] ?? 'NERO') ?>" style="height:32px;width:32px;">
            </a>
            <p class="nero-footer-tagline mt-3">
                <?= Html::encode(Yii::t('app', 'footer.tagline')) ?>
            </p>
        </div>
        <div>
            <div class="nero-footer-col-title"><?= Html::encode(Yii::t('app', 'footer.navTitle')) ?></div>
            <div class="nero-footer-links">
                <a href="<?= Url::to(['/site/index', '#' => 'services']) ?>"><?= Html::encode(Yii::t('app', 'nav.services')) ?></a>
                <!-- <a href="<?= Url::to(['/site/index', '#' => 'works']) ?>"><?= Html::encode(Yii::t('app', 'nav.works')) ?></a> -->
                <a href="<?= Url::to(['/site/index', '#' => 'pricing']) ?>"><?= Html::encode(Yii::t('app', 'nav.pricing')) ?></a>
                <a href="<?= Url::to(['/site/index', '#' => 'branches']) ?>"><?= Html::encode(Yii::t('app', 'nav.branches')) ?></a>
                <a href="<?= Url::to(['/site/index', '#' => 'contact']) ?>"><?= Html::encode(Yii::t('app', 'nav.contact')) ?></a>
            </div>
        </div>
        <div>
            <div class="nero-footer-col-title"><?= Html::encode(Yii::t('app', 'footer.contactTitle')) ?></div>
            <div class="nero-footer-contact">
                <?php foreach ($phoneNumbers as $phoneNumber): ?>
                    <div><?= Html::encode($phoneNumber) ?></div>
                <?php endforeach; ?>
                <div><?= Html::encode($address) ?></div>
                <div><?= Html::encode(Yii::t('app', 'contact.hoursValue')) ?></div>
            </div>
        </div>
    </div>
    <div class="nero-footer-bottom">
        <?= Html::encode(Yii::t('app', 'footer.rights')) ?>
    </div>
</footer>
