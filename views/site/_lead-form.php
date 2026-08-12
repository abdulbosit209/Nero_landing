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
$phoneOwners = Yii::$app->params['phoneOwners'] ?? [];
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
                    <?php foreach ($phoneNumbers as $i => $phoneNumber): ?>
                        <div class="nero-contact-phone-row">
                            <a class="nero-contact-phone" href="<?= Html::encode('tel:' . preg_replace('/[^+\d]/', '', $phoneNumber)) ?>"><?= Html::encode($phoneNumber) ?></a>
                            <?php if (isset($phoneOwners[$i])): ?>
                                <span class="nero-contact-phone-owner"><?= Html::encode(Yii::t('app', 'contact.phoneOwner.' . $phoneOwners[$i])) ?></span>
                            <?php endif; ?>
                        </div>
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

        <?php
        // The user only ever types the 9 digits after the country code; "+998" is a
        // fixed prefix they can't edit or accidentally delete. The visible input here
        // is display-only (not posted) — it formats digits live as (90) 001-00-10 and
        // mirrors the plain "+998XXXXXXXXX" value into the real, hidden model field
        // that actually gets submitted/validated (see JS below).
        $phonePlaceholder = Html::encode(Yii::t('app', 'form.placeholder.phoneNumber'));
        ?>
        <?= $form->field($model, 'phoneNumber', [
            'template' => "{label}\n"
                . '<div class="nero-phone-field">'
                . '<span class="nero-phone-prefix">+998</span>'
                . '<input type="tel" id="lead-form-phone-display" class="form-control" '
                . 'inputmode="numeric" autocomplete="tel-national" maxlength="14" '
                . 'placeholder="' . $phonePlaceholder . '">'
                . '{input}'
                . "</div>\n{error}",
        ])
            ->label(Yii::t('app', 'form.label.phoneNumber'), $labelOptions + ['for' => 'lead-form-phone-display'])
            ->hiddenInput() ?>

        <?= $form->field($model, 'service', ['template' => "{label}\n{input}\n{error}"])
            ->label(Yii::t('app', 'form.label.service'), $labelOptions)
            ->dropDownList(LeadForm::serviceOptions(), ['prompt' => Yii::t('app', 'form.service.placeholder')]) ?>

        <div class="nero-form-row">
            <div class="nero-date-field" id="lead-form-date-field">
                <?= $form->field($model, 'preferredDate', ['template' => "{label}\n{input}\n{error}"])
                    ->label(Yii::t('app', 'form.label.preferredDate'), $labelOptions)
                    ->input('date', [
                        'id' => 'lead-form-date',
                        'min' => date('Y-m-d'),
                        'autocomplete' => 'off',
                    ]) ?>
                <span class="nero-date-placeholder-text" aria-hidden="true"><?= Html::encode(Yii::t('app', 'form.date.placeholder')) ?></span>
            </div>

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

        <?= Html::submitButton(Html::encode(Yii::t('app', 'form.submit')), [
            'id' => 'lead-form-submit-btn',
            'class' => 'nero-submit-btn',
            'data-label-idle' => Yii::t('app', 'form.submit'),
            'data-label-loading' => Yii::t('app', 'form.submitting'),
        ]) ?>

        <?php ActiveForm::end(); ?>
    </div>
</section>

<?php $this->registerJs(<<<'JS'
(function () {
    // Splits whatever digits the user typed/pasted into the (XX) XXX-XX-XX groups of
    // a 9-digit local number, formatting progressively so it looks right mid-type too.
    function formatPhoneDigits(digits) {
        var out = '';
        if (digits.length > 0) {
            out += '(' + digits.slice(0, 2);
        }
        if (digits.length >= 2) {
            out += ') ';
        }
        if (digits.length > 2) {
            out += digits.slice(2, 5);
        }
        if (digits.length > 5) {
            out += '-' + digits.slice(5, 7);
        }
        if (digits.length > 7) {
            out += '-' + digits.slice(7, 9);
        }
        return out;
    }

    // Strips everything but digits, then drops a leading "998" country code if one
    // slipped in (e.g. pasting/autofilling a full "+998901234567" number).
    function normalizeDigits(raw) {
        var digits = raw.replace(/\D/g, '');
        if (digits.length > 9 && digits.slice(0, 3) === '998') {
            digits = digits.slice(3);
        }
        return digits.slice(0, 9);
    }

    var display = document.getElementById('lead-form-phone-display');
    var hidden = document.getElementById('leadform-phonenumber');
    if (!display || !hidden) {
        return;
    }

    // Redisplaying the form after a failed submit re-renders the hidden field with
    // the previously submitted value — seed the visible input from it so the user
    // doesn't see a blank field next to their (still-filled-in) phone number.
    var initialDigits = normalizeDigits(hidden.value);
    if (initialDigits) {
        display.value = formatPhoneDigits(initialDigits);
    }

    display.addEventListener('input', function () {
        var caret = display.selectionStart;
        var digitsBeforeCaret = display.value.slice(0, caret).replace(/\D/g, '').length;

        var digits = normalizeDigits(display.value);
        var formatted = formatPhoneDigits(digits);
        display.value = formatted;

        // Re-place the caret after the same count of digits it followed before
        // reformatting, so editing mid-number doesn't kick the cursor to the end.
        var pos = 0;
        var seen = 0;
        while (pos < formatted.length && seen < digitsBeforeCaret) {
            if (/\d/.test(formatted[pos])) {
                seen++;
            }
            pos++;
        }
        display.setSelectionRange(pos, pos);

        hidden.value = digits.length ? ('+998' + digits) : '';
    });

    // Yii's client validation binds to 'change'/'blur' on the bound field (not every
    // keystroke) — mirror that here so an in-progress number doesn't flash an error
    // before the user has finished typing it.
    display.addEventListener('blur', function () {
        hidden.dispatchEvent(new Event('change', { bubbles: true }));
    });

    // Yii's client-side validation toggles `is-invalid` on the actual bound field,
    // which is the hidden input here and so never renders — mirror that class onto
    // the visible display input so the red-border state still shows to the user.
    if (window.MutationObserver) {
        var observer = new MutationObserver(function () {
            display.classList.toggle('is-invalid', hidden.classList.contains('is-invalid'));
        });
        observer.observe(hidden, { attributes: true, attributeFilter: ['class'] });
    }
})();

(function () {
    // The "Request a Service" CTAs link here with ?service=<slug> (pricing cards) or
    // ?focus=service (header/hero, which aren't tied to a specific service) so arriving
    // at the form either preselects the matching service or just puts focus on the
    // dropdown as the natural next field to fill in.
    var select = document.getElementById('leadform-service');
    if (!select) {
        return;
    }
    var params = new URLSearchParams(window.location.search);
    var service = params.get('service');
    if (service) {
        var hasOption = Array.prototype.some.call(select.options, function (option) {
            return option.value === service;
        });
        if (hasOption) {
            select.value = service;
        }
    }
    if (service || params.has('focus')) {
        select.focus();
    }
})();

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
    var dateField = document.getElementById('lead-form-date-field');
    if (!dateInput) {
        return;
    }

    // Native date inputs render their own locale placeholder (e.g. "dd/mm/yyyy")
    // that can't be overridden via the `placeholder` attribute. We hide that native
    // text (see .is-empty rule in CSS) and show our own "Select a date" label on
    // top instead, toggled to match whether a value is actually selected.
    function syncEmptyState() {
        if (dateField) {
            dateField.classList.toggle('is-empty', !dateInput.value);
        }
    }
    syncEmptyState();
    dateInput.addEventListener('change', syncEmptyState);

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

(function () {
    // Yii's ActiveForm JS (loaded as part of YiiAsset, which AppAsset depends on)
    // triggers 'beforeSubmit' on the form only once client validation has passed and
    // the real submit is about to happen — the correct hook for a loading state, as
    // opposed to the plain 'submit' event which also fires on the initial,
    // validation-only pass. This is a full (non-AJAX) POST, so the button just needs
    // to stay disabled until the browser navigates away with the redirect response.
    var form = document.getElementById('lead-form-form');
    var button = document.getElementById('lead-form-submit-btn');
    if (!form || !button || !window.jQuery) {
        return;
    }

    var loadingLabel = button.dataset.labelLoading;

    jQuery(form).on('beforeSubmit', function () {
        button.disabled = true;
        button.classList.add('is-loading');
        if (loadingLabel) {
            button.textContent = loadingLabel;
        }
    });

    // A back/forward navigation can restore this exact page (with the button still
    // disabled) from the browser's bfcache instead of reloading it — reset the button
    // so the form isn't stuck unusable if the user navigates back to it.
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            button.disabled = false;
            button.classList.remove('is-loading');
            button.textContent = button.dataset.labelIdle || button.textContent;
        }
    });
})();
JS
) ?>
