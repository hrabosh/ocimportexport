<?php

namespace OpenCartImporter\Services;

use OpenCartImporter\Models\Product;
use OpenCartImporter\Models\ProductDescription;
use OpenCartImporter\Models\ProductToCategory;
use OpenCartImporter\Models\ProductAttribute;
use OpenCartImporter\Models\ProductFilter;
use OpenCartImporter\Models\ProductToLayout;
use OpenCartImporter\Models\ProductToStore;

use Illuminate\Support\Facades\DB;

class ProductImport
{
    protected $batchSize = 1000; // Number of records to process in each batch
    protected $modelMap = [];
    protected $language_id = 2;
    protected $mappings = [];

    public function __construct()
    {
        $config = include __DIR__ . '/../Config/ProductImportMapping.php';
        $this->mappings = $config['mappings'];
    }

    public function import($filePath)
    {
        $loader = new ExcelLoader($filePath);

        $loader->getData(function ($chunk) {
            $this->processBatch($chunk, $this->mappings);
        });
        unset($chunk);
    }

    private function processBatch(array $batch, array $mappings)
    {
        $products = [];
        $descriptions = [];
        $categories = [];
        $attributes = [];
        $filters = [];
        $additionalData = [];
        
        $start = microtime(true);
        foreach ($batch as $row) {
            $productData = [];
            $descriptionData = [];
            $categoryData = [];
            $attributeData = [];
            $filterData = [];
    
            foreach ($mappings['oc_product'] as $excelColumn => $dbColumn) {
                    $productData[$dbColumn] = $row[$excelColumn] ?? '';
                    if ($excelColumn == 'lang' && $row[$excelColumn] == 'cz') {
                        $this->language_id = 2;
                    }
            }
    
            foreach ($mappings['oc_product_description'] as $excelColumn => $dbColumn) {
                if (isset($row[$excelColumn])) {
                    if ($excelColumn == 'lang' && $row[$excelColumn] == 'cz') {
                        $descriptionData[$dbColumn] = $this->language_id;
                    } else {
                        $descriptionData[$dbColumn] = $row[$excelColumn];
                    }
                }
            }
    
            foreach ($mappings['oc_product_to_category'] as $excelColumn => $dbColumn) {
                if (isset($row[$excelColumn])) {
                    $categoryData[$dbColumn] = $row[$excelColumn];
                }
            }
    
            foreach ($mappings['oc_product_attribute'] as $excelColumn => $dbColumn) {
                if (isset($row[$excelColumn]) && preg_match('/attribute_(\d+)/', $dbColumn, $matches)) {
                    $attributeId = $matches[1];
                    $attributeData[$attributeId] = $row[$excelColumn];
                } 
            }            
    
            foreach ($mappings['oc_product_filter'] as $excelColumn => $dbColumn) {
                if (isset($row[$excelColumn])) {
                    $filterData[$dbColumn] = $row[$excelColumn];
                }
            }
    
            if (isset($productData['model'])) {
                $model = $productData['model'];
                $products[] = $productData;
                $additionalData[$model] = ['store_id' => 0, 'layout_id' => 0];
    
                if (!empty($descriptionData)) {
                    $descriptions[] = $descriptionData;
                }
                if (!empty($categoryData)) {
                    $categories[$model] = $categoryData;
                }
                if (!empty($attributeData)) {
                    $attributes[$model] = $attributeData;
                }
                if (!empty($filterData)) {
                    $filters[$model] = $filterData;
               
                }
            }
        }

        
        // Perform batch operations
        $productIds = $this->updateOrCreateProducts($products);
        $this->updateDescriptions($descriptions, $productIds);
        
        $this->updateCategories($categories, $productIds);
        $this->updateAttributes($attributes, $productIds);
        //$this->updateFilters($filters, $productIds);
        $this->updateAdditional($additionalData, $productIds); 
    }

    private function updateOrCreateProducts(array $products)
    {
            Product::upsert(
                $products,
                ['model'], // Unique key(s)
                array_keys(reset($products)) // Fields to update
            );
    
        // Retrieve product IDs after upsert
        $productIds = Product::whereIn('model', array_column($products, 'model'))
            ->pluck('product_id', 'model')
            ->toArray();
    
        return $productIds;
    }
    
    private function updateDescriptions(array $descriptions, array $productIds)
    {
        // Map model to product_id
        $descriptionsWithProductId = array_map(function($description) use ($productIds) {
            if (isset($productIds[$description['model']])) {
                $description['product_id'] = $productIds[$description['model']];
                $description['language_id'] = $this->language_id;
                unset($description['model']);
            } 
            return $description;
        }, $descriptions);

        // Perform upsert operation
        ProductDescription::upsert(
            $descriptionsWithProductId,
            ['product_id', 'language_id'], // Unique keys
            array_keys(reset($descriptionsWithProductId)) // Fields to update
        );
    }
    
    private function updateCategories(array $categories, array $productIds)
    {
        $categoryRecords = collect($categories)
            ->map(function($categoryData, $model) use ($productIds) {
                return [
                    'product_id' => $productIds[$model],
                    'category_id' => $categoryData['category_id']
                ];
            })
            ->all();
        
        // Perform upsert operation
        ProductToCategory::upsert(
            $categoryRecords,
            ['product_id', 'category_id'], // Unique keys
            ['category_id'] // Fields to update
        );
    }

    private function updateAttributes(array $attributes, array $productIds)
    {
        $attributeRecords = collect($attributes)
            ->flatMap(function($attributesData, $model) use ($productIds) {
                return collect($attributesData)->map(function($value, $attributeId) use ($productIds, $model) {
                    return [
                        'product_id' => $productIds[$model],
                        'attribute_id' => $attributeId,
                        'language_id' => $this->language_id,
                        'text' => $value
                    ];
                })->all();
            })
            ->all();

        // Perform upsert operation
        ProductAttribute::upsert(
            $attributeRecords,
            ['product_id', 'attribute_id', 'language_id'], // Unique keys
            ['text'] // Fields to update
        );
    }

    private function updateFilters(array $filters, array $productIds)
    {
        $filterRecords = collect($filters)
            ->flatMap(function($filterData, $model) use ($productIds) {
                return collect(explode(',', $filterData['filter_ids']))->map(function($filterId) use ($productIds, $model) {
                    return [
                        'product_id' => $productIds[$model],
                        'filter_id' => trim($filterId)
                    ];
                })->all();
            })
            ->all();

        // Perform upsert operation
        ProductFilter::upsert(
            $filterRecords,
            ['product_id', 'filter_id'], // Unique keys
            ['filter_id'] // Fields to update
        );
    }

    private function updateAdditional(array $additionalData, array $productIds)
    {
        $layoutRecords = collect($additionalData)
            ->map(function($data, $model) use ($productIds) {
                return [
                    'product_id' => $productIds[$model],
                    'layout_id' => $data['layout_id'],
                    'store_id' => $data['store_id']
                ];
            })
            ->all();

        $storeRecords = collect($additionalData)
            ->map(function($data, $model) use ($productIds) {
                return [
                    'product_id' => $productIds[$model],
                    'store_id' => $data['store_id']
                ];
            })
            ->all();

        // Perform upsert operations
        ProductToLayout::upsert(
            $layoutRecords,
            ['product_id', 'store_id'], // Unique keys
            ['layout_id'] // Fields to update
        );

        ProductToStore::upsert(
            $storeRecords,
            ['product_id', 'store_id'], // Unique keys
            ['store_id'] // Fields to update
        );
    }

}
