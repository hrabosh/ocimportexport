<?php
namespace OpenCartImporter\Services;
error_reporting(E_ALL);
ini_set('display_errors', 1);

use OpenCartImporter\Database\DBConnection;
use OpenCartImporter\Models\Product;
use OpenCartImporter\Models\ProductDescription;
use OpenCartImporter\Models\ProductToCategory;
use Maatwebsite\Excel\ExcelServiceProvider;
use Illuminate\Container\Container;
use Maatwebsite\Excel\Facades\Excel;

class ProductService
{

    public function __construct(array $db)
    {   
        DBConnection::initialize($db);
    }

    public function importProducts($filePath)
    {

        $loader = new ProductImport();
        $loader->import($filePath);
    }

    public function exportProducts()
    {
        //return \Maatwebsite\Excel\Facades\Excel::download(new OpenCartImporter\Services\ProductsExport, 'products.xlsx');
    }
}
