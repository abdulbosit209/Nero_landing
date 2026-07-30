<?php

// Database connection, wired into the 'db' component in config/web.php and
// config/console.php. Not currently used by any feature (LeadForm/ContactForm are
// plain yii\base\Model, not ActiveRecord) — safe to leave with default/placeholder
// credentials until a feature actually needs persistence.
return [
    'class' => \yii\db\Connection::class,
    'dsn' => 'mysql:host=localhost;dbname=yii2basic',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
