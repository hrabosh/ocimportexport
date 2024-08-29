<?php

use LeKoala\SpreadCompat\Csv\League;
use LeKoala\SpreadCompat\Csv\Native;
use LeKoala\SpreadCompat\Csv\OpenSpout;
use LeKoala\SpreadCompat\Csv\PhpSpreadsheet;
use LeKoala\SpreadCompat\Xlsx\Native as XlsxNative;
use LeKoala\SpreadCompat\Xlsx\PhpSpreadsheet as XlsxPhpSpreadsheet;
use LeKoala\SpreadCompat\Xlsx\OpenSpout as XlsxOpenSpout;
use LeKoala\SpreadCompat\Xlsx\Simple;

ini_set('log_errors', 1);
ini_set('error_log', 'test.log'); // Specify the log file
error_log('Custom error logging enabled.');

require 'vendor/autoload.php';

$largeCsv = __DIR__ . '/tests/data/large.csv';
$largeXlsx = 'export_test.xlsx';

$csv = [
    League::class,
    OpenSpout::class,
    Native::class,
    PhpSpreadsheet::class
];

$xlsx = [
    Simple::class,
    //XlsxOpenSpout::class,
    //XlsxPhpSpreadsheet::class,
    XlsxNative::class,
];

$reps = 5;

$times = [];
$memory = [];
/*
foreach ($csv as $cl) {
    foreach (range(1, $reps) as $i) {

        $inst = new ($cl);

        $st = microtime(true);
        $data = iterator_to_array($inst->readFile($largeCsv));
        $et = microtime(true);
        $diff = $et - $st;
        $times['csv'][$cl][] = $diff;
    }
}
*/

foreach ($xlsx as $cl) {
        /** @var \LeKoala\SpreadCompat\Xlsx\XlsxAdapter $inst */
        $inst = new ($cl);

        $st = microtime(true);
        $data = iterator_to_array($inst->readFile($largeXlsx));
        $et = microtime(true);
        $diff = $et - $st;
        $times['xlsx'][$cl][] = $diff;
        $memory['xlsx'][$cl]['max_memory'] = memory_get_peak_usage(true);
    
}


foreach ($times as $format => $dataFormat) {
    echo "Results for $format" . PHP_EOL;

    $results = [];
    foreach ($dataFormat as $class => $times) {
        $averageTime = round(array_sum($times) / count($times), 4);
        $results[$class] = $averageTime;
    }

    uasort($results, fn ($a, $b) => $a <=> $b);
    foreach ($results as $class => $averageTime) {
        echo "$class : " . $averageTime . PHP_EOL;
    }

    echo PHP_EOL;
}

print_r($memory);