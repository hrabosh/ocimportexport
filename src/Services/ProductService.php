<?php
namespace OpenCartImporter\Services;
use OpenCartImporter\Logger\Logger;
use Monolog\Handler\StreamHandler;

error_reporting(E_ALL);
ini_set('display_errors', 1);

use OpenCartImporter\Database\DBConnection;
use OpenCartImporter\Proxy\ProxyGenerator;

class ProductService
{
    private $logger;

    public function __construct(array $db)
    {   
        DBConnection::initialize($db);
        $this->logger = new Logger();
    }

    public function importProducts($filePath)
    {   
        $startTime = microtime(true);
        $this->logger->logInfo("Starting product import from $filePath");

        try {
            echo 'Memory usage before import ' . memory_get_usage(true);
            $productImport = new ProductImport($this->logger);
            $productImport->import($filePath);
            
            echo 'Memory usage after import ' . memory_get_usage(true);

            $endTime = microtime(true);
            $this->logger->logExecutionTime('Product import', $startTime, $endTime);
        } catch (\Exception $e) {
            $this->logger->logError('An error occurred during product import', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function exportProducts()
    {
        //return \Maatwebsite\Excel\Facades\Excel::download(new OpenCartImporter\Services\ProductsExport, 'products.xlsx');
    }
}
