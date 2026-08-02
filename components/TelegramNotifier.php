<?php

declare(strict_types=1);

namespace app\components;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Yii;
use app\models\LeadForm;
use yii\helpers\Html;
use yii\web\UploadedFile;

/**
 * Delivers lead-form submissions to a Telegram chat via the Bot API.
 */
class TelegramNotifier
{
    /** Telegram accepts far larger photos, but this keeps uploads fast and consistent
     *  regardless of what the phone camera originally produced. */
    private const MAX_PHOTO_BYTES = 2 * 1024 * 1024;

    private readonly string $_botToken;
    private readonly string $_chatId;
    private readonly Client $_client;

    /**
     * @param Client|null $client Test seam for injecting a mock/pre-configured Guzzle
     *  client. Deliberately untyped (not `?Client`): Yii's DI container auto-instantiates
     *  ANY class-typed constructor parameter it builds — including nullable ones with a
     *  null default — by calling `Yii::createObject(Client::class)`, which produces a
     *  bare `new Client([])` with no `base_uri`. That would silently defeat the
     *  `base_uri`-configured client built below on every framework-driven construction
     *  (e.g. SiteController's constructor-injected TelegramNotifier), causing every
     *  Telegram API call to fail with "URI must include a scheme and host". Leaving the
     *  parameter untyped means the container passes it through as plain `null` instead,
     *  so only explicit test code (`new TelegramNotifier($token, $chatId, $mockClient)`)
     *  ever supplies a real Client here.
     */
    public function __construct(?string $botToken = null, ?string $chatId = null, mixed $client = null)
    {
        $this->_botToken = $botToken ?? (string) (Yii::$app->params['telegramBotToken'] ?? '');
        $this->_chatId = $chatId ?? (string) (Yii::$app->params['telegramChatId'] ?? '');
        $this->_client = $client instanceof Client ? $client : new Client([
            'base_uri' => "https://api.telegram.org/bot{$this->_botToken}/",
            'timeout' => 10,
        ]);
    }

    public function sendLead(LeadForm $model): bool
    {
        if ($this->_botToken === '' || $this->_chatId === '') {
            Yii::warning('TelegramNotifier: bot token/chat id not configured, skipping send.', __METHOD__);

            return false;
        }

        $text = $this->buildMessage($model);
        $photos = $model->photos;

        try {
            if (count($photos) === 0) {
                $this->_client->post('sendMessage', [
                    'form_params' => [
                        'chat_id' => $this->_chatId,
                        'text' => $text,
                        'parse_mode' => 'HTML',
                    ],
                ]);
            } elseif (count($photos) === 1) {
                $this->sendSinglePhoto($photos[0], $text);
            } else {
                $this->sendPhotoAlbum($photos, $text);
            }

            return true;
        } catch (GuzzleException $e) {
            Yii::error('Telegram notify failed: ' . $e->getMessage(), __METHOD__);

            return false;
        }
    }

    /**
     * Sends a single photo with the lead details as its caption, via the simpler
     * sendPhoto endpoint (used instead of sendMediaGroup when there's just one photo).
     */
    private function sendSinglePhoto(UploadedFile $photo, string $caption): void
    {
        [$contents, $filename] = $this->preparePhoto($photo);

        $this->_client->post('sendPhoto', [
            'multipart' => [
                ['name' => 'chat_id', 'contents' => $this->_chatId],
                ['name' => 'caption', 'contents' => $caption],
                ['name' => 'parse_mode', 'contents' => 'HTML'],
                [
                    'name' => 'photo',
                    'contents' => $contents,
                    'filename' => $filename,
                ],
            ],
        ]);
    }

    /**
     * Sends multiple photos as a single Telegram album via sendMediaGroup. Each photo is
     * attached as its own multipart part, referenced from the `media` JSON array by the
     * `attach://<name>` convention the Bot API requires; the caption is only set on the
     * first item since Telegram shows it once for the whole album.
     *
     * @param UploadedFile[] $photos
     */
    private function sendPhotoAlbum(array $photos, string $caption): void
    {
        $multipart = [
            ['name' => 'chat_id', 'contents' => $this->_chatId],
        ];

        $media = [];
        foreach ($photos as $i => $photo) {
            $attachName = "photo{$i}";
            $item = ['type' => 'photo', 'media' => "attach://{$attachName}"];
            if ($i === 0) {
                $item['caption'] = $caption;
                $item['parse_mode'] = 'HTML';
            }
            $media[] = $item;

            [$contents, $filename] = $this->preparePhoto($photo);
            $multipart[] = [
                'name' => $attachName,
                'contents' => $contents,
                'filename' => $filename,
            ];
        }

        $multipart[] = ['name' => 'media', 'contents' => json_encode($media)];

        $this->_client->post('sendMediaGroup', ['multipart' => $multipart]);
    }

    /**
     * Compresses a photo under MAX_PHOTO_BYTES if needed and returns its contents
     * paired with the filename Telegram should see — re-encoding (when it happens)
     * always produces a JPEG, so the extension is corrected to match.
     *
     * @return array{0: string, 1: string}
     */
    private function preparePhoto(UploadedFile $photo): array
    {
        $contents = ImageCompressor::compress($photo->tempName, self::MAX_PHOTO_BYTES);

        $originalSize = filesize($photo->tempName);
        if ($originalSize !== false && $originalSize <= self::MAX_PHOTO_BYTES) {
            return [$contents, $photo->name];
        }

        $filename = preg_replace('/\.[^.\/]+$/', '', $photo->name) . '.jpg';

        return [$contents, $filename];
    }

    private function buildMessage(LeadForm $model): string
    {
        $brandName = (string) (Yii::$app->params['brandName'] ?? 'Nero Auto Service');

        $lines = [
            '<b>New lead — ' . Html::encode($brandName) . '</b>',
            'Name: ' . Html::encode($model->name),
            'Phone: ' . Html::encode($model->phoneNumber),
            'Service: ' . Html::encode(LeadForm::serviceOptions()[$model->service] ?? $model->service),
            'Date: ' . Html::encode($model->preferredDate),
            'Time: ' . Html::encode($model->preferredTime),
        ];

        if ($model->description !== '') {
            $lines[] = 'Description: ' . Html::encode($model->description);
        }

        return implode("\n", $lines);
    }
}
