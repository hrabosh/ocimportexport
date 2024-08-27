<?php
require 'vendor/autoload.php';


ini_set('log_errors', 1);
ini_set('error_log', 'error.log'); // Specify the log file
error_log('Custom error logging enabled.');


$inputFileName = 'export_test.xlsx';
$inputFileType = 'Xlsx';
/*
$callStartTime = microtime(true);

$reader = IOFactory::createReader('Xlsx');

$spreadsheet = $reader->load($inputFileName);

$callEndTime = microtime(true);
$loadCallTime = $callEndTime - $callStartTime;

echo PHP_EOL;
echo 'Call time to load spreadsheet file was ' , sprintf('%.4f', $loadCallTime) , ' seconds' , PHP_EOL;

echo ' Current memory usage: ' , (memory_get_usage(true)) , ' KB' , PHP_EOL;

$worksheet = $spreadsheet->getActiveSheet();
echo "Worksheet Name: {$worksheet->getTitle()}", PHP_EOL;
echo "Range Dimensions specified in the xlsx file {$worksheet->calculateWorksheetDimension()} that will be used by the toArray() method", PHP_EOL;
echo "Range of cells that contain actual data {$worksheet->calculateWorksheetDataDimension()} that can be passed to the rangeToArray() method", PHP_EOL;

$data = $worksheet->rangeToArray($worksheet->calculateWorksheetDataDimension(), null, true, true, true);

// Echo memory usage
echo ' Current memory usage: ' , (memory_get_usage(true)) , ' KB' , PHP_EOL;
echo '    Peak memory usage: ' , (memory_get_peak_usage(true)) , ' KB' , PHP_EOL;
*/
$db = [
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'username' => 'root',
    'password' => '19962512',
    'prefix' => 'hk_',
    'database' => 'opencart',
];

echo 'memoryy usage before init productservice class ' . memory_get_usage(true);
$productService = new OpenCartImporter\Services\ProductService($db);
$productService->importProducts($inputFileName);
