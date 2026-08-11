<?php

declare(strict_types=1);

/**
 * "Bizning filiallarimiz" section: renders a Yandex Maps map with a marker per
 * branch. Branch data (name/address/coords/phone) is a static JS array in
 * web/js/branches-map.js — edit that file to add real branches. The API key
 * comes from params.yandexMapsApiKey (config/params.php / params-local.php /
 * YANDEX_MAPS_API_KEY env var), defaulting to the YOUR_API_KEY placeholder.
 *
 * @var yii\web\View $this
 */

use yii\helpers\Html;
use yii\web\View;

$apiKey = Yii::$app->params['yandexMapsApiKey'] ?? 'YOUR_API_KEY';

// Yandex Maps only ships a handful of locales (ru_RU, en_US, en_RU, tr_TR, uk_UA) —
// map the app's current language to the closest one, falling back to English.
$ymapsLangs = ['ru' => 'ru_RU', 'en' => 'en_US'];
$ymapsLang = $ymapsLangs[Yii::$app->language] ?? 'en_US';

// Registered before the Yandex script (both POS_END) so it defines window.initNeroMap
// first; the async Yandex script calls that name itself via its own onload= param
// once it finishes loading, so load order between the two script tags doesn't matter.
$this->registerJsFile('@web/js/branches-map.js', ['position' => View::POS_END]);
$this->registerJsFile(
    'https://api-maps.yandex.ru/2.1/?apikey=' . urlencode($apiKey) . '&lang=' . $ymapsLang . '&onload=initNeroMap',
    ['position' => View::POS_END, 'async' => true],
);
?>
<section id="branches" class="nero-section nero-section-surface">
    <div class="nero-section-head">
        <div class="nero-eyebrow"><?= Html::encode(Yii::t('app', 'branches.sectionEyebrow')) ?></div>
        <h2 class="nero-heading"><?= Html::encode(Yii::t('app', 'branches.sectionTitle')) ?></h2>
        <p class="nero-sub"><?= Html::encode(Yii::t('app', 'branches.sectionSub')) ?></p>
    </div>

    <div id="nero-map" class="nero-branches-map"></div>
</section>
