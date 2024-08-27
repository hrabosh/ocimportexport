<?php
namespace OpenCartImporter\Services;

ini_set('memory_limit', '512M');
ini_set('max_execution_time', '1000');

use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelLoader
{
    private $filePath;
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function getData(callable $sendData)
    {
        $callStartTime = microtime(true);

        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true); // Only load cell values, not styles or formatting
        $reader->setReadEmptyCells(false);

        $chunkFilter = new ChunkReadFilter();
        $reader->setReadFilter($chunkFilter);

        $totalRows = $reader->listWorksheetInfo($this->filePath )[0]['totalRows'];
        $headings = null;

        for($startRow = 2; $startRow <= $totalRows; $startRow += 1000){
            $chunkFilter->setRows($startRow, 1000);  
            $spreadsheet = $reader->load($this->filePath);

            $worksheet = $spreadsheet->getActiveSheet();

            $chunk = $worksheet->toArray(null, false, false, true);

                        // Remove completely empty rows
            $chunk = array_filter($chunk, function ($row) {
                return array_filter($row); // Only keep rows with data
            });

            if ($headings === null) {
                $headings = array_shift($chunk);
            } else {
                array_shift($chunk); // Remove the first row from the chunk
            }

            array_walk(
                $chunk,
                function (&$row) use ($headings) {
                    $row = array_combine($headings, $row);
                }
            );
        
            echo 'memory before: ' . memory_get_usage(true);
            $sendData($chunk);
            echo 'Current memory usage 1: ' . memory_get_usage(true);

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $chunk);
            echo 'Current memory usage 2: ' . memory_get_usage(true);
            sleep(1);

        }

        // Echo memory usage
        //$this->logger->logExecutionTime('Excel loader', $callStartTime, microtime(true));
        error_log('Current memory usage: ' . (memory_get_usage(true) / 1024) . ' KB');
        error_log('Peak memory usage: ' , (memory_get_peak_usage(true) / 1024) , ' KB');
    }
}

class ChunkReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
{
    private $startRow = 0;
    private $endRow   = 0;

    /**  Set the list of rows that we want to read  */
    public function setRows($startRow, $chunkSize) {
        $this->startRow = $startRow;
        $this->endRow   = $startRow + $chunkSize;
    }

    public function readCell($columnAddress, $row, $worksheetName = ''):bool {
        //  Only read the heading row, and the configured rows
        if (($row == 1) || ($row >= $this->startRow && $row < $this->endRow)) {
            return true;
        }
        return false;
    }
}

