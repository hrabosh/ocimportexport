<?php
namespace OpenCartImporter\Services;

ini_set('memory_limit', '2048M');
ini_set('max_execution_time', '1000');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx\ReaderEntityFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Support\Collection;
use OpenCartImporter\Models\Product;
use OpenCartImporter\Models\ProductDescription;
use OpenCartImporter\Models\ProductToCategory;
use OpenCartImporter\Models\ProductAttribute;
use OpenCartImporter\Models\ProductFilter;

class ExcelLoader
{
    private $filePath;
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function getData()
    {
        $callStartTime = microtime(true);

        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true); // Only load cell values, not styles or formatting
        $reader->setReadEmptyCells(false);

        $spreadsheet = $reader->load($this->filePath);

        $callEndTime = microtime(true);
        $loadCallTime = $callEndTime - $callStartTime;

        error_log('Call time to load spreadsheet file was ' . sprintf('%.4f', $loadCallTime) . ' seconds');

        error_log('Current memory usage: ' . (memory_get_usage(true) / 1024) . ' KB');

        $worksheet = $spreadsheet->getActiveSheet();


        error_log("Range Dimensions specified in the xlsx file {$worksheet->calculateWorksheetDimension()} that will be used by the toArray() method");
        error_log("Range of cells that contain actual data {$worksheet->calculateWorksheetDataDimension()} that can be passed to the rangeToArray() method");

        $data = $worksheet->rangeToArray($worksheet->calculateWorksheetDataDimension(), null, true, true, true);

        $headings = array_shift($data);
        array_walk(
            $data,
            function (&$row) use ($headings) {
                $row = array_combine($headings, $row);
            }
        );

        // Echo memory usage
        error_log('Current memory usage: ' . (memory_get_usage(true) / 1024) . ' KB');
        error_log('Peak memory usage: ' , (memory_get_peak_usage(true) / 1024) , ' KB');

        return $data;
    }
}