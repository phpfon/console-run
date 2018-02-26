<?php

$combination = null;
if (count($argv) > 1 && strlen($argv[1]) == 15) {
    $combination = $argv[1];
} else {
    echo 'Укажите комбинацию';
    exit;
}

function prepareSQL15($combination) {
    $sql = 'SELECT code FROM result WHERE code REGEXP \'' . $combination . '\'';
    return $sql;
}

function prepareSQL14($combination) {
    $sql = 'SELECT code FROM result WHERE 1 = 0';
    for($i = 0; $i < 15; $i ++) {
        $comb = $combination;
        $comb[$i] = '.';
        $sql .= PHP_EOL . ' OR code REGEXP \'' . $comb . '\' ';
    }
    return $sql;
}

function prepareSQL13($combination) {
    $sql = 'SELECT code FROM result WHERE 1 = 0';
    for($i = 0; $i < 15; $i ++) {
        $comb = $combination;
        $comb[$i] = '.';
        for($j = 0; $j < 15; $j ++) {
            if ($i == $j) continue;

            $comb2 = $comb;
            $comb2[$j] = '.';
            $sql .= PHP_EOL . ' OR code REGEXP \'' . $comb2 . '\' ';
        }
    }
    return $sql;
}

function getData($sql) {
    $dsn = "mysql:host=localhost;dbname=fonbet;charset=utf8";
    $opt = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, 'root', 'root', $opt);

    return $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
}

echo 'Количество 15: ' . count(getData(prepareSQL15($combination))) . PHP_EOL;
echo 'Количество 14: ' . count(getData(prepareSQL14($combination))) . PHP_EOL;
echo 'Количество 13: ' . count(getData(prepareSQL13($combination))) . PHP_EOL;


