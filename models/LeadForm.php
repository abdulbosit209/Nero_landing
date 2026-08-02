<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use app\components\TelegramNotifier;
use yii\base\Model;
use yii\web\UploadedFile;

/**
 * LeadForm is the model behind the landing page's "get a quote" submission form.
 */
class LeadForm extends Model
{
    private const PHONE_PATTERN = '/^\+998\d{9}$/';

    /** Telegram's sendMediaGroup accepts at most 10 items; keep a comfortable margin below that. */
    public const MAX_PHOTOS = 4;

    /** Bookable hours: the picker only offers whole-hour slots within business hours. */
    public const TIME_START_HOUR = 8;
    public const TIME_END_HOUR = 22;

    public string $name = '';
    public string $phoneNumber = '';
    public string $service = '';
    public string $description = '';
    public string $preferredDate = '';
    public string $preferredTime = '';

    /** @var UploadedFile[] */
    public array $photos = [];

    public function beforeValidate(): bool
    {
        $this->phoneNumber = preg_replace('/\s+/', '', $this->phoneNumber) ?? '';

        return parent::beforeValidate();
    }

    /**
     * @return array the validation rules.
     */
    public function rules(): array
    {
        return [
            [['name', 'phoneNumber', 'service', 'preferredDate', 'preferredTime'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['phoneNumber'], 'match', 'pattern' => self::PHONE_PATTERN, 'message' => Yii::t('app', 'form.error.phoneFormat')],
            [['service'], 'in', 'range' => array_keys(self::serviceOptions())],
            [['description'], 'string', 'max' => 2000],
            [
                ['preferredDate'],
                'date',
                'format' => 'php:Y-m-d',
                'min' => date('Y-m-d'),
                'tooSmall' => Yii::t('app', 'form.error.dateInPast'),
                'message' => Yii::t('app', 'form.error.dateFormat'),
            ],
            [
                ['preferredTime'],
                'in',
                'range' => array_keys(self::timeOptions()),
                'message' => Yii::t('app', 'form.error.timeFormat'),
            ],
            // '!photos' (not 'photos') keeps this attribute out of safeAttributes(), so
            // load()/setAttributes() never mass-assigns it — it's still fully validated
            // via activeAttributes(), which strips the '!' prefix. This matters because
            // Html::activeFileInput() always renders a hidden `name="photos[]" value=""`
            // alongside the file input (so isset() detects "submitted" even with no file
            // chosen); a real multipart form submission sends both parts, so
            // $_POST['LeadForm']['photos'] would otherwise contain a stray '' entry —
            // assigning that into the typed `UploadedFile[] $photos` property is exactly
            // the kind of raw-POST assignment this class avoids. SiteController::
            // actionSubmitLead() instead sets $model->photos explicitly from
            // UploadedFile::getInstances() after load(), which is the value this rule
            // actually validates. 'maxFiles' caps how many photos a single lead can attach.
            // maxSize matches docker/uploads.ini's upload_max_filesize (15M) — the raw
            // phone-camera original as received; TelegramNotifier::preparePhoto()
            // compresses each photo down under 2MB afterward, before it's relayed on.
            [
                ['!photos'],
                'file',
                'extensions' => 'png, jpg, jpeg, webp',
                'maxSize' => 15 * 1024 * 1024,
                'maxFiles' => self::MAX_PHOTOS,
                'skipOnEmpty' => true,
                'tooMany' => Yii::t('app', 'form.error.tooManyPhotos', ['limit' => self::MAX_PHOTOS]),
            ],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels(): array
    {
        return [
            'name' => Yii::t('app', 'form.label.name'),
            'phoneNumber' => Yii::t('app', 'form.label.phoneNumber'),
            'service' => Yii::t('app', 'form.label.service'),
            'description' => Yii::t('app', 'form.label.description'),
            'photos' => Yii::t('app', 'form.label.photo'),
            'preferredDate' => Yii::t('app', 'form.label.preferredDate'),
            'preferredTime' => Yii::t('app', 'form.label.preferredTime'),
        ];
    }

    /**
     * Builds the dropdown options for the "service" field from LandingContent::SERVICES,
     * so the form always offers exactly the services shown in the "Services" section
     * above it, plus a catch-all "other" option.
     *
     * @return array<string, string> service slug => translated label
     */
    public static function serviceOptions(): array
    {
        $options = [];
        foreach (LandingContent::SERVICES as $slug) {
            $options[$slug] = Yii::t('app', "services.$slug.title");
        }

        $options['other'] = Yii::t('app', 'form.service.other');

        return $options;
    }

    /**
     * Builds the dropdown options for the "preferred time" field: whole-hour slots only,
     * from TIME_START_HOUR to TIME_END_HOUR inclusive (e.g. "08:00" .. "22:00").
     *
     * @return array<string, string> "HH:00" => "HH:00"
     */
    public static function timeOptions(): array
    {
        $options = [];
        for ($hour = self::TIME_START_HOUR; $hour <= self::TIME_END_HOUR; $hour++) {
            $value = sprintf('%02d:00', $hour);
            $options[$value] = $value;
        }

        return $options;
    }

    /**
     * Validates the model and, if valid, relays the lead to Telegram.
     * Telegram delivery failures are logged internally and never block a successful result.
     */
    public function send(TelegramNotifier $notifier): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $notifier->sendLead($this);

        return true;
    }
}
