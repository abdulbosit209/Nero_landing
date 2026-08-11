<?php

declare(strict_types=1);

/**
 * Contact/lead-capture section: business contact details plus the "get a quote" form.
 * Submits to SiteController::actionSubmitLead(), which validates via LeadForm and
 * relays the lead to Telegram (see components/TelegramNotifier.php).
 *
 * @var yii\web\View $this
 * @var app\models\LeadForm $model
 */

use app\models\LeadForm;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$phoneNumbers = Yii::$app->params['phoneNumbers'] ?? [];
$address = Yii::$app->params['address'] ?? '';
// Field labels are visually hidden (placeholders carry the visible hint) but remain in
// the DOM for screen readers.
$labelOptions = ['class' => 'visually-hidden form-label'];
?>
<section id="contact" class="nero-section">
    <div id="contact-grid">
        <div>
            <div class="nero-eyebrow"><?= Html::encode(Yii::t('app', 'contact.sectionEyebrow')) ?></div>
            <h2 class="nero-heading" style="font-size:44px;"><?= Html::encode(Yii::t('app', 'contact.sectionTitle')) ?></h2>
            <p class="nero-sub" style="max-width:400px;margin-bottom:32px;">
                <?= Html::encode(Yii::t('app', 'contact.sectionSub')) ?>
            </p>
            <div class="nero-contact-info-row">
                <div>
                    <div class="nero-contact-label"><?= Html::encode(Yii::t('app', 'contact.phoneLabel')) ?></div>
                    <?php foreach ($phoneNumbers as $phoneNumber): ?>
                        <a class="nero-contact-phone" href="<?= Html::encode('tel:' . preg_replace('/[^+\d]/', '', $phoneNumber)) ?>"><?= Html::encode($phoneNumber) ?></a>
                    <?php endforeach; ?>
                </div>
                <div>
                    <div class="nero-contact-label"><?= Html::encode(Yii::t('app', 'contact.addressLabel')) ?></div>
                    <div class="nero-contact-value"><?= Html::encode($address) ?></div>
                </div>
                <div>
                    <div class="nero-contact-label"><?= Html::encode(Yii::t('app', 'contact.hoursLabel')) ?></div>
                    <div class="nero-contact-value"><?= Html::encode(Yii::t('app', 'contact.hoursValue')) ?></div>
                </div>
            </div>
        </div>

        <?php $form = ActiveForm::begin([
            'id' => 'lead-form-form',
            'action' => ['site/submit-lead'],
            'options' => ['enctype' => 'multipart/form-data', 'class' => 'nero-form-card'],
        ]); ?>

        <?= $form->field($model, 'name', ['template' => "{label}\n{input}\n{error}"])
            ->label(Yii::t('app', 'form.label.name'), $labelOptions)
            ->textInput(['placeholder' => Yii::t('app', 'form.placeholder.name')]) ?>

        <?= $form->field($model, 'phoneNumber', ['template' => "{label}\n{input}\n{error}"])
            ->label(Yii::t('app', 'form.label.phoneNumber'), $labelOptions)
            ->textInput(['type' => 'tel', 'placeholder' => Yii::t('app', 'form.placeholder.phoneNumber')]) ?>

        <?= $form->field($model, 'service', ['template' => "{label}\n{input}\n{error}"])
            ->label(Yii::t('app', 'form.label.service'), $labelOptions)
            ->dropDownList(LeadForm::serviceOptions(), ['prompt' => Yii::t('app', 'form.service.placeholder')]) ?>

        <div class="nero-form-row">
            <?= $form->field($model, 'preferredDate', ['template' => "{label}\n{input}\n{error}"])
                ->label(Yii::t('app', 'form.label.preferredDate'), $labelOptions)
                ->input('date', [
                    'id' => 'lead-form-date',
                    'min' => date('Y-m-d'),
                    'autocomplete' => 'off',
                ]) ?>

            <?= $form->field($model, 'preferredTime', ['template' => "{label}\n{input}\n{error}"])
                ->label(Yii::t('app', 'form.label.preferredTime'), $labelOptions)
                ->dropDownList(
                    LeadForm::timeOptions(),
                    ['prompt' => Yii::t('app', 'form.time.placeholder')]
                ) ?>
        </div>

        <?= $form->field($model, 'description', ['template' => "{label}\n{input}\n{error}"])
            ->label(Yii::t('app', 'form.label.description'), $labelOptions)
            ->textarea(['rows' => 3, 'placeholder' => Yii::t('app', 'form.placeholder.description')]) ?>

        <div>
            <label id="lead-form-photo-label" class="nero-file-label">
                <?= Html::encode(Yii::t('app', 'form.upload')) ?>
                <?= $form->field($model, 'photos[]', [
                    'template' => '{input}{error}',
                    'options' => ['tag' => false],
                ])->fileInput([
                    // No `capture` attribute here on purpose: `capture` tells mobile
                    // browsers to jump straight into the camera for a single shot, which
                    // overrides/bypasses the gallery's multi-select picker on most mobile
                    // browsers — combined with `multiple`, that silently limited every
                    // mobile user to one photo. Without it, tapping this button shows the
                    // OS's normal "choose files" sheet (camera or gallery, multi-select).
                    'accept' => 'image/*',
                    'multiple' => true,
                    'style' => 'display:none;',
                    'aria-label' => Yii::t('app', 'form.label.photo'),
                ]) ?>
            </label>
            <?php
            // Thumbnails of the currently selected files, each with a remove (x) button —
            // populated/updated by JS below as the user adds/removes photos. Native file
            // inputs have no way to drop a single file from the selection, so removal
            // works by rebuilding the input's FileList via the DataTransfer API.
            ?>
            <div
                id="lead-form-photo-previews"
                class="nero-photo-preview-grid"
                data-remove-label="<?= Html::encode(Yii::t('app', 'form.removePhoto')) ?>"
                data-max-files="<?= LeadForm::MAX_PHOTOS ?>"
            ></div>
        </div>

        <?= Html::submitButton(Html::encode(Yii::t('app', 'form.submit')), ['class' => 'nero-submit-btn']) ?>

        <?php ActiveForm::end(); ?>
    </div>
</section>

<?php $this->registerJs(<<<'JS'
(function () {
    var input = document.getElementById('leadform-photos');
    var previews = document.getElementById('lead-form-photo-previews');
    var uploadLabel = document.getElementById('lead-form-photo-label');
    if (!input || !previews) {
        return;
    }
    var removeLabel = previews.dataset.removeLabel || 'Remove';
    var maxFiles = parseInt(previews.dataset.maxFiles, 10) || 4;

    // A native <input type="file"> REPLACES its selection every time the picker is
    // used — it never appends. So if a user taps "upload", picks one photo, then taps
    // again and picks another, the second pick would silently wipe out the first one.
    // We keep our own running list across picker invocations and resync it into the
    // input via DataTransfer after every change/removal, so selections accumulate the
    // way a user actually expects "pick more than one photo" to work.
    var selectedFiles = [];

    function syncInput() {
        var dt = new DataTransfer();
        selectedFiles.forEach(function (file) {
            dt.items.add(file);
        });
        input.files = dt.files;
    }

    function render() {
        previews.innerHTML = '';
        selectedFiles.forEach(function (file, index) {
            var item = document.createElement('div');
            item.className = 'nero-photo-preview-item';

            var img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.alt = file.name;
            item.appendChild(img);

            var remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'nero-photo-preview-remove';
            remove.setAttribute('aria-label', removeLabel);
            remove.textContent = '×';
            remove.addEventListener('click', function () {
                selectedFiles.splice(index, 1);
                syncInput();
                render();
            });
            item.appendChild(remove);

            previews.appendChild(item);
        });

        if (uploadLabel) {
            var atLimit = selectedFiles.length >= maxFiles;
            input.disabled = atLimit;
            uploadLabel.classList.toggle('is-disabled', atLimit);
        }
    }

    input.addEventListener('change', function () {
        Array.prototype.forEach.call(input.files, function (file) {
            if (selectedFiles.length < maxFiles) {
                selectedFiles.push(file);
            }
        });
        syncInput();
        render();
    });
})();

(function () {
    // The date field must only be set via the native picker, never typed — this keeps
    // submitted dates well-formed without extra client-side parsing. We deliberately
    // avoid the `readonly` attribute: readonly inputs are "immutable" per spec, and
    // showPicker() throws on an immutable input, which would block the picker too.
    // Blocking keyboard entry by hand achieves the same "pick, don't type" result
    // while leaving the field mutable enough for showPicker() to keep working.
    var dateInput = document.getElementById('lead-form-date');
    if (!dateInput) {
        return;
    }

    function openPicker() {
        if (typeof dateInput.showPicker === 'function') {
            try {
                dateInput.showPicker();
            } catch (err) {
                // Ignore: some browsers throw if a picker is already open.
            }
        }
    }

    dateInput.addEventListener('keydown', function (e) {
        if (e.key !== 'Tab') {
            e.preventDefault();
        }
    });
    dateInput.addEventListener('paste', function (e) {
        e.preventDefault();
    });
    dateInput.addEventListener('mousedown', function (e) {
        e.preventDefault();
        dateInput.focus();
        openPicker();
    });
    dateInput.addEventListener('focus', openPicker);
})();
JS
) ?>
