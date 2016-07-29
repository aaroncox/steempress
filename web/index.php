<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new SteemPress\Application('prod');
$app['http_cache']->run();
