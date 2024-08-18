<?php
namespace OpenCartImporter\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Support\Collection;
use OpenCartImporter\Models\Product;
use OpenCartImporter\Models\ProductDescription;
use OpenCartImporter\Models\ProductToCategory;
use OpenCartImporter\Models\ProductAttribute;
use OpenCartImporter\Models\ProductFilter;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class ChunkReadFilter implements IReadFilter
{
    private $startRow;
    private $endRow;

    public function setRows($startRow, $chunkSize)
    {
        $this->startRow = $startRow;
        $this->endRow = $startRow + $chunkSize;
    }

    public function readCell($column, $row, $worksheetName = '')
    {
        if ($row >= $this->startRow && $row <= $this->endRow) {
            return true;
        }
        return false;
    }
}

Class ProductImportOptimalized
{
 
    protected $batchSize = 1000; // Number of records to process in each batch
    protected $modelMap = [];
    protected $language_id = 2;

    public function import($filePath)
    {
        $config = include __DIR__ . '/../Config/product_import.php';
        $mappings = $config['mappings'];

        $reader = IOFactory::createReader('Xlsx');
        $chunkFilter = new ChunkReadFilter();

        $reader->setReadFilter($chunkFilter);
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);

        $spreadsheet = $reader->load($filePath);

        $worksheet = $spreadsheet->getActiveSheet();

        $chunkSize = 1000;
        $startRow = 1;
        $highestRow = $worksheet->getHighestRow();

        while ($startRow <= $highestRow) {
            $chunkFilter->setRows($startRow, $chunkSize);
            $this->processBatch($worksheet, $mappings, $startRow, $chunkSize);
            $startRow += $chunkSize;

            // Release memory
            gc_collect_cycles();
        }
    }

    private function worksheetToCollection(Worksheet $worksheet)
    {
        $rows = [];
        $highestRow = $worksheet->getHighestDataRow(); // e.g. 10
        $highestColumn = $worksheet->getHighestDataColumn(); // e.g 'F'
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 5

        // Extract header row
        $headers = [];
        for ($col = 1; $col <= $highestColumnIndex; ++$col) {
            $cell = $worksheet->getCellByColumnAndRow($col, 1);
            $headers[$col] = $cell->getValue();
        }

        // Extract data rows
        for ($row = 2; $row <= $highestRow; $row++) {
            $rowArray = [];
            for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                $cell = $worksheet->getCellByColumnAndRow($col, $row);
                $header = $headers[$col] ?? $col; // Use column index if header is missing
                $rowArray[$header] = $cell->getValue();
            }
            $rows[] = $rowArray;
        }

        return new Collection($rows);
    }

    private function processBatch(Worksheet $worksheet, $mappings, $startRow, $chunkSize)
    {
        // Initialize arrays to store batch data
        $products = [];
        $descriptions = [];
        $categories = [];
        $attributes = [];
        $filters = [];
    
        $highestColumn = $worksheet->getHighestDataColumn(); 
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 5
    
        // Extract data from worksheet for the current batch
        for ($row = $startRow; $row < $startRow + $chunkSize && $row <= $worksheet->getHighestRow(); $row++) {
            $rowArray = [];
            for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                $cell = $worksheet->getCellByColumnAndRow($col, $row);
                print_r($cell->getValue());
                $rowArray[$worksheet->getCellByColumnAndRow($col, 1)->getValue()] = $cell->getValue();
            }
            // Process row as before
            $this->mapAndCollectRowData($rowArray, $mappings, $products, $descriptions, $categories, $attributes, $filters);
        }

        var_dump($products);
        var_dump($descriptions);    
        // Bulk update with collected data
        $this->updateOrCreateProducts($products);
        $this->updateDescriptions($descriptions);
        $this->updateCategories($categories);
        $this->updateAttributes($attributes);
        $this->updateFilters($filters);
    
        // Release memory
        unset($products, $descriptions, $categories, $attributes, $filters);
        gc_collect_cycles();
    }

    private function mapAndCollectRowData(array $row, array $mappings, array &$products, array &$descriptions, array &$categories, array &$attributes, array &$filters)
    {
    // Initialize or clear batch data for this row
    $productData = [];
    $descriptionData = [];
    $categoryData = [];
    $attributeData = [];
    $filterData = [];

    // Map product data
    foreach ($mappings['oc_product'] as $excelColumn => $dbColumn) {
        if (isset($row[$excelColumn])) {
            $productData[$dbColumn] = $row[$excelColumn];

            if ($excelColumn == 'lang' && $row[$excelColumn] == 'cz') {
                $this->language_id = 2;
            }
        }
    }

    // Map description data
    foreach ($mappings['oc_product_description'] as $excelColumn => $dbColumn) {
        if (isset($row[$excelColumn])) {
            $descriptionData[$dbColumn] = $row[$excelColumn];
        }
    }

    // Map category data
    foreach ($mappings['oc_product_to_category'] as $excelColumn => $dbColumn) {
        if (isset($row[$excelColumn])) {
            $categoryData[$dbColumn] = $row[$excelColumn];
        }
    }

    // Map attribute data
    foreach ($mappings['oc_product_attribute'] as $excelColumn => $dbColumn) {
        if (isset($row[$excelColumn])) {
            // Extract attribute_id from attribute column name (e.g., 'attribute_1')
            if (preg_match('/attribute_(\d+)/', $dbColumn, $matches)) {
                $attributeId = $matches[1];
                $attributeData[$attributeId] = $row[$excelColumn];
            }
        }
    }

    // Map filter data
    foreach ($mappings['oc_product_filter'] as $excelColumn => $dbColumn) {
        if (isset($row[$excelColumn])) {
            $filterData[$dbColumn] = $row[$excelColumn];
        }
    }

    // Collect data for batch processing
    if (isset($productData['model'])) {
        // Collect product data
        $products[$productData['model']] = $productData;

        // Collect additional data if necessary
        $additionalData[$productData['model']] = [
            'store_id' => isset($productData['store_id']) ? $productData['store_id'] : 0,
            'layout_id' => isset($productData['layout_id']) ? $productData['layout_id'] : 0
        ];
    }

    // Collect description data
    if (!empty($descriptionData) && isset($productData['model'])) {
        $descriptions[$productData['model']] = $descriptionData;
    }

    // Collect category data
    if (!empty($categoryData) && isset($productData['model'])) {
        $categories[$productData['model']] = $categoryData;
    }

    // Collect attribute data
    if (!empty($attributeData) && isset($productData['model'])) {
        $attributes[$productData['model']] = $attributeData;
    }

    // Collect filter data
    if (!empty($filterData) && isset($productData['model'])) {
        $filters[$productData['model']] = $filterData;
    }
}

    private function updateOrCreateProducts(array $products)
    {
        // To store product IDs for later use
        $productIds = [];

        foreach ($products as $model => $productData) {
            // Update or create product and get the product ID
            $product = Product::updateOrCreate(['model' => $model], $productData);
            $productIds[$model] = $product->product_id; // Map model to product_id
        }

        return $productIds;
    }

    private function updateDescriptions(array $descriptions)
    {
        foreach ($descriptions as $model => $descriptionData) {
            if (isset($model)) {
                $productId = Product::where('model', $model)->value('product_id');
                if ($productId) {
                    ProductDescription::updateOrCreate(
                        [
                        'product_id' => $productId, 
                        'language_id' => $this->language_id
                        ],
                        $descriptionData
                    );
                }
            }
        }
    }

    private function updateCategories(array $categories)
    {
        foreach ($categories as $model => $categoryData) {
            if (isset($model)) {
                $productId = Product::where('model', $model)->value('product_id');
                if ($productId) {
                    ProductToCategory::updateOrCreate(
                        ['product_id' => $productId, 'category_id' => $categoryData['category_id']]
                    );
                }
            }
        }
    }

    private function updateAttributes(array $attributes)
    {
        foreach ($attributes as $model => $attributesData) {
            if (isset($model)) {
                $productId = Product::where('model', $model)->value('product_id');

                foreach ($attributesData as $attributeId => $value) {
                        ProductAttribute::updateOrCreate(
                            [
                                'product_id' => $productId,
                                'attribute_id' => $attributeId,
                                'language_id' => $this->language_id
                            ],
                            ['text' => $value]
                        );
                }
            }
        }
    }

    private function updateFilters(array $filters)
    {
        foreach ($filters as $model => $filterData) {
            if (isset($model)) {
                $productId = Product::where('model', $model)->value('product_id');
                if ($productId) {
                    $filterIds = explode(',', $filterData['filter_ids']);
                
                    // Clear existing filters
                    ProductFilter::where('product_id', $productId)->delete();
    
                    // Update/Create new filters
                    foreach ($filterIds as $filterId) {
                        ProductFilter::create([
                            'product_id' => $productId,
                            'filter_id' => trim($filterId),
                        ]);
                    }
                }
            }
        }
    }

    private function updateAdditional(array $additionalData)
    {
        foreach ($additionalData as $model => $data) {
            if (isset($model)) {
                $product = Product::where('model', $model)->first();
                //$productId = Product::where('model', $model)->value('product_id');
                $product->toLayout()->updateOrCreate([
                    'layout_id' => $data['layout_id'],
                    'store_id' => $data['store_id']
                ]);
                $product->toStore()->updateOrCreate(['store_id' => $data['store_id']]);
            }
        }
    }

    private function logMemoryUsage($message)
    {
        $memoryUsage = memory_get_usage(true) / 1024 / 1024; // In MB
        $memoryPeak = memory_get_peak_usage(true) / 1024 / 1024; // In MB
        echo "[DEBUG] $message - Memory Usage: {$memoryUsage}MB, Peak: {$memoryPeak}MB\n";
    }   
}