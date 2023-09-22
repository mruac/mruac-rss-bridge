<?php

if (version_compare(\PHP_VERSION, '7.4.0') === -1) {
    exit('RSS-Bridge requires minimum PHP version 7.4.0!');
}

require_once __DIR__ . '/lib/bootstrap.php';

if (
    array_key_exists('__key', $_GET)
    && $_GET['__key'] === getenv("__key")
) {
    unset($_GET['__key']);
    $rssBridge = new RssBridge();

    $rssBridge->main($argv ?? []);
} else {
    http_response_code(403);
    die('Forbidden.');
}
