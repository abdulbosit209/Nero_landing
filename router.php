<?php

/**
 * Router script for PHP's built-in web server (`php -S`), used locally via
 * `php yii serve --router=router.php` and in the Dockerfile's CMD.
 *
 * Unlike Apache, the built-in server doesn't consult web/.htaccess, so without
 * this router, pretty URLs (config/web.php urlManager) would 404 on any path
 * that isn't a real file — it forwards those requests to web/index.php while
 * still serving real static assets (CSS/JS/images) directly.
 */

$root = __DIR__ . '/web';
$path = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = realpath($root . $path);

if ($file !== false && is_file($file) && str_starts_with($file, $root . DIRECTORY_SEPARATOR)) {
    return false;
}

require $root . '/index.php';
