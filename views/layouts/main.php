<?php

declare(strict_types=1);

/**
 * Shared HTML skeleton for every page: <head> assets, header, flash alerts, the
 * page's own $content, and footer.
 *
 * @var yii\web\View $this
 * @var string $content
 */

use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\helpers\Html;

$this->render('_head');
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100" data-bs-theme="light">
<head>
    <?php $this->head() ?>
    <title><?= Html::encode($this->title) ?></title>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<?= $this->render('_header') ?>

<main id="main" class="flex-grow-1" role="main">
    <?php if (!empty($this->params['breadcrumbs'])): ?>
        <div class="container">
            <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
        </div>
    <?php endif ?>
    <?= Alert::widget() ?>
    <?= $content ?>
</main>

<?= $this->render('_footer') ?>

<?php $this->registerJs(<<<'JS'
(function () {
    // The success/error flash toast (styled in site.css) has no fixed lifetime of its
    // own — without this it just sits until the user notices the close button, which
    // is easy to miss since it renders bottom-fixed, away from wherever the user's
    // attention actually is right after a redirect. Auto-dismissing (with a fade so
    // it doesn't just vanish) makes sure it was actually seen, while the close button
    // still works for anyone who wants it gone sooner.
    var alerts = document.querySelectorAll('#main > .alert');
    alerts.forEach(function (alert) {
        var timer = setTimeout(function () {
            dismiss();
        }, 6000);

        function dismiss() {
            clearTimeout(timer);
            alert.classList.add('nero-toast-out');
            alert.addEventListener('animationend', function () {
                alert.remove();
            });
        }

        // Don't fight a manual close (Bootstrap's own dismiss already removes the
        // element); just stop the pending auto-dismiss from also touching it.
        alert.addEventListener('closed.bs.alert', function () {
            clearTimeout(timer);
        });
    });
})();
JS
) ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
