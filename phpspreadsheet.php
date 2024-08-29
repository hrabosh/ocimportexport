<?php
require 'vendor/autoload.php';


ini_set('log_errors', 1);
ini_set('error_log', 'error.log'); // Specify the log file
error_log('Custom error logging enabled.');


$inputFileName = 'export_test.xlsx';
$inputFileType = 'Xlsx';

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

