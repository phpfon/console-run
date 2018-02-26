<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap.php';

$fileName = 'csv/849.csv';

$data = file($fileName);

$packageCodes = [];
$perPackage = 150;
$packageCount = 0;

$packageItems = [];
foreach ($data as $v) {
    $packageCount ++;
    $packageItems[] = [
        'Amount' => [
            'Value' => 50
        ],
        'Options' => [
            ['Options' => $v[0]],
            ['Options' => $v[1]],
            ['Options' => $v[2]],
            ['Options' => $v[3]],
            ['Options' => $v[4]],
            ['Options' => $v[5]],
            ['Options' => $v[6]],
            ['Options' => $v[7]],
            ['Options' => $v[8]],
            ['Options' => $v[9]],
            ['Options' => $v[10]],
            ['Options' => $v[11]],
            ['Options' => $v[12]],
            ['Options' => $v[13]],
            ['Options' => $v[14]]
        ]
    ];

    if ($packageCount === $perPackage) {
        //send request
        $packageCode = '';

        $client = new \GuzzleHttp\Client([]);

        $url = 'https://clientsapi01.bkfon-resource.ru/superexpress-info/DataService.svc/CreateVirtualPackage';
        $params = [
            'Accept' => 'application/json, text/plain, */*',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Accept-Language' => 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
            'Connection' => 'keep-alive',
            'Content-Type' => 'application/json',
            'Host' => 'clientsapi01.bkfon-resource.ru',
            'Origin' => 'https://www.fonbet.ru',
            'Referer' => 'https://www.fonbet.ru/superexpress-info/?locale=ru&pageDomain=https://www.fonbet.ru',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36',
            'X-Compress' => 'null'
        ];

        $body = json_encode([
            'drawingId' => 849,
            'raw' => $packageItems
        ]);

        $response = $client->post($url, [
            'body' => $body,
            'headers' => $params
        ]);

        $json = $response->getBody()->getContents();
        return json_decode($json, true);

        $packageItems = [];
        $packageCodes[] = $packageCode;
    }
    $result .= ('50.00; 1-(' . $v[0] . '); 2-(' . $v[1] . '); 3-(' . $v[2] . '); 4-(' . $v[3] . '); 5-(' . $v[4] . '); 6-(' .
        $v[5] . '); 7-(' . $v[6] . '); 8-(' . $v[7] . '); 9-(' . $v[8] . '); 10-(' . $v[9] . '); 11-(' . $v[10] . '); 12-(' .
        $v[11] . '); 13-(' . $v[12] . '); 14-(' . $v[13] . '); 15-(' . $v[14] . ').' . PHP_EOL);
}

file_put_contents('csv/849_package_codes.txt', implode(PHP_EOL, $packageCodes));

