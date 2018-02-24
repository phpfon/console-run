<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap.php';

use app\services\BetService;

$startPage = null;
if (count($argv) > 1) {
    $startPage = (int) $argv[1];
}

$betService = new BetService();
$betService->getBets($startPage);


