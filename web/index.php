<?php

declare(strict_types=1);

// Defaults to the previous hardcoded dev/debug behavior for local development, but lets
// a production deploy (see Dockerfile/render.yaml) set YII_ENV=prod and YII_DEBUG=0 via
// environment variables instead of editing this file per environment.
defined('YII_DEBUG') or define('YII_DEBUG', (getenv('YII_DEBUG') ?: '1') === '1');
defined('YII_ENV') or define('YII_ENV', getenv('YII_ENV') ?: 'dev');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
