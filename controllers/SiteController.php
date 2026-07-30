<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use app\components\TelegramNotifier;
use app\models\ContactForm;
use app\models\LeadForm;
use yii\captcha\CaptchaAction;
use yii\filters\VerbFilter;
use yii\mail\MailerInterface;
use yii\web\Controller;
use yii\web\ErrorAction;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * Handles the public-facing pages: the landing page and its lead-capture form, plus
 * the stock template's contact/about pages.
 *
 * $mailer and $telegram are constructor-promoted and resolved by Yii's DI container
 * (Yii::createController() builds controllers through Yii::createObject()), so no
 * explicit wiring is needed beyond the MailerInterface singleton registered in
 * config/web.php — Yii autowires TelegramNotifier from its own constructor defaults.
 */
class SiteController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly MailerInterface $mailer,
        private readonly TelegramNotifier $telegram,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'submit-lead' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'error' => [
                'class' => ErrorAction::class,
            ],
            'captcha' => [
                'class' => CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
                'transparent' => true,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        return $this->render('index', ['leadForm' => new LeadForm()]);
    }

    /**
     * Handles the landing page lead-capture form submission.
     *
     * @return Response|string
     */
    public function actionSubmitLead(): Response|string
    {
        $model = new LeadForm();

        if ($model->load($this->request->post())) {
            $model->photos = UploadedFile::getInstances($model, 'photos');

            if ($model->send($this->telegram)) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'form.flash.success'));

                return $this->redirect(['index', '#' => 'contact']);
            }
        }

        return $this->render('index', ['leadForm' => $model]);
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact(): Response|string
    {
        $model = new ContactForm();

        $contact = $model->load($this->request->post()) && $model->contact(
            $this->mailer,
            Yii::$app->params['adminEmail'],
            Yii::$app->params['senderEmail'],
            Yii::$app->params['senderName'],
        );

        if ($contact) {
            Yii::$app->session->setFlash(
                'success',
                'Thank you for contacting us. We will respond to you as soon as possible.',
            );

            return $this->refresh();
        }

        return $this->render('contact', ['model' => $model]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout(): string
    {
        return $this->render('about');
    }
}
