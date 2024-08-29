<?php
namespace OpenCartImporter\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelLoader
{
    private $filePath;
    private $chunkSize = 1000;

    public function __construct($filePath, $chunkSize = 1000)
    {
        $this->filePath = $filePath;
        $this->chunkSize = $chunkSize;
    }

    public function getData(callable $sendData)
    {
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);

        $chunkFilter = new ChunkReadFilter();
        $reader->setReadFilter($chunkFilter);

        $totalRows = $reader->listWorksheetInfo($this->filePath)[0]['totalRows'];
        $headings = null;

        for ($startRow = 2; $startRow <= $totalRows; $startRow += $this->chunkSize) {
            error_log('Memory startting iteration:' . memory_get_usage(true));
            $chunkFilter->setRows($startRow, $this->chunkSize);
            $spreadsheet = $reader->load($this->filePath);
            error_log('Memory after loading file:' . memory_get_usage(true));
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

            error_log('memory before processbatch: ' . memory_get_usage(true));
            $sendData($chunk);
            error_log('memory after processing:' . memory_get_usage(true));
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $chunk, $worksheet);
            gc_collect_cycles();
            error_log('memory after unseting in excel: ' . memory_get_usage(true));
        }

        error_log('Final memory usage: ' . (memory_get_usage(true) / 1024) . ' KB');
        error_log('Peak memory usage: ' . (memory_get_peak_usage(true) / 1024) . ' KB');
    }

    private function mapHeadingsToData(array $chunk, array $headings)
    {
        return array_map(function ($row) use ($headings) {
            return array_combine($headings, $row);
        }, $chunk);
    }
}

class ChunkReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
{
    private $startRow = 0;
    private $endRow = 0;

    public function setRows($startRow, $chunkSize)
    {
        $this->startRow = $startRow;
        $this->endRow = $startRow + $chunkSize;
    }

    public function readCell($columnAddress, $row, $worksheetName = ''): bool
    {
        return ($row == 1) || ($row >= $this->startRow && $row < $this->endRow);
    }
}
