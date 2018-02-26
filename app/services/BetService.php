<?php

namespace app\services;

use app\models\BetItem;
use GuzzleHttp\Client;
use app\models\Bet;

class BetService
{
    public $betItemsCheck;

    public function getBets($startPage)
    {
        $result = [];
        $data = $this->getJsonData();

        $hour = (int)$data['d']['Summary']['ActiveRest']['Hours'];
        $minutes = (int)$data['d']['Summary']['ActiveRest']['Minutes'];
        $minutes += $hour * 60;

        $data = $data['d']['Items'];
        foreach ($data as $item) {
            $date = $item['Expired'];
            $matches = [];
            preg_match('/\d+/', $date, $matches);
            $date = (int) $matches[0];
            $date = date('Y-m-d', $date / 1000);
            $id = (int)$item['Id'];

            $bet = Bet::where('number', $id)->first();
            if (!$bet instanceof Bet) {
                $bet = new Bet();
                $bet->fill([
                    'number' => $id,
//                'win_code' => $this->getWinCode($id),
                    'date' => $date
                ]);

                $bet->save();
            }

            $this->betItemsCheck = $this->getBetItemsCheck($bet->id);

            echo 'Розыгрыш № ' . $bet->number . PHP_EOL;
            $this->createBetItems($bet, $minutes, $startPage);

            sleep(2);
        }

        return $result;
    }

    protected function getBetItemsCheck($betId)
    {
        $result = [];
        $betItems = BetItem::where('bet_id', $betId)->get();
        /** @var BetItem $betItem */
        foreach ($betItems as $betItem) {
            $result[$betItem->code] = $betItem;
        }

        return $result;
    }

    protected function createBetItems(Bet $bet, $minute, $startPage)
    {
        $startPage = $startPage ? $startPage : 0;
        $perPage = 150;
        $firstPageData = $this->getBetItemsPage($bet->number, $startPage, $perPage);
        $totalCount = $firstPageData['totalCount'];

        echo 'Всего страниц  ' . number_format($totalCount/$perPage, 2) . PHP_EOL;

        for($page = $startPage + 1; $page < $totalCount/$perPage; $page++) {
            $data = $this->getBetItemsPage($bet->number, $page,$perPage);
            $items = $data['items'];

            foreach ($items as $item) {
                $code = $item['Code'];
                $count = $item['Count'];
                $betItem = BetItem::where('bet_id', $bet->id)->where('code', $code)->first();
                if (!$betItem instanceof BetItem) {
                    $betItem = new BetItem();
                    $betItem->fill([
                        'bet_id' => $bet->id,
                        'code' => $code,
                        'minute' => $minute,
                        'count' => $count
                    ]);
                    $betItem->count = $count;
                    $betItem->save();
                    echo ' - добавлена комбинация ' . $code . PHP_EOL;
                }
            }

            echo ' Выгружена страница  ' . $page . PHP_EOL;
        }
    }

    protected function getBetItemsPage($betNumber, $page,$perPage)
    {
        $url = 'https://clientsapi01.bkfon-resource.ru/superexpress-info/DataService.svc/GetStakeDict';

        $client = new Client();
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
            'options' => [
                'DrawingId'     => $betNumber,
                'StartFrom'     => $perPage * $page,
                'Count'         => $perPage
            ]
        ]);

        $response = $client->post($url, [
            'body' => $body,
            'headers' => $params
        ]);

        $json = $response->getBody()->getContents();
        $data = json_decode($json, true);

        return [
            'totalCount' => $data['d']['Summary']['TotalCount'],
            'items' => $data['d']['Items']
        ];
    }

    protected function getJsonData()
    {
        $client = new Client([]);

        $url = 'https://clientsapi01.bkfon-resource.ru/superexpress-info/DataService.svc/SelectDrawings';
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
            'sp' => [
                'StartFrom'     => 0,
                'Count'         => 20,
                'SortField'     => 'Expired',
                'SortDir'       => 'DESC',
                'Culture'       => 'ru-RU',
                'TimeZoneId'    => '',
                'TimeZoneOffset'=> -100,
//                'State'         => [0, 1]
                'State'         => [2]
            ]
        ]);

        $response = $client->post($url, [
            'body' => $body,
            'headers' => $params
        ]);

        $json = $response->getBody()->getContents();
        return json_decode($json, true);
    }

    protected function getWinCode($betId)
    {
        $url = 'https://clientsapi01.bkfon-resource.ru/superexpress-info/DataService.svc/GetDrawing';
        $client = new Client();
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
        $response = $client->post($url, [
            'body' => '{"id":' . $betId . '}',
            'headers' => $params
        ]);

        $json = $response->getBody()->getContents();
        $data = json_decode($json, true);

        $events = $data['d']['Details']['Events'];
        $code = '';
        foreach ($events as $event) {
            $code .= $event['ResultCode'];
        }

        return $code;
    }
}