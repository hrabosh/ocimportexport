<?php
namespace OpenCartImporter\Services;

use LeKoala\SpreadCompat\Xlsx\Native as XlsxNative;

class NativeExcelLoader
{
    private $filePath;
    private $chunkSize;

    public function __construct($filePath, $chunkSize = 1000)
    {
        $this->filePath = $filePath;
        $this->chunkSize = $chunkSize;
    }

    public function getData(callable $sendData)
    {
        $inst = new XlsxNative();
        $chunkSize = 1000;  // Adjust this as needed
    
        $st = microtime(true);
    
        $currentChunk = [];
        $chunkCount = 0;

        //assoc will give header as array keys
        foreach ($inst->readFile($this->filePath, assoc:true) as $row) {
            $currentChunk[] = $row;
        
            if (count($currentChunk) >= $chunkSize) {
                error_log("Processed chunk #$chunkCount. Memory usage before sends: " . (memory_get_usage(true) / 1024) . ' KB');
                $sendData($currentChunk);
                error_log("Processed chunk #$chunkCount. Memory usage after sends: " . (memory_get_usage(true) / 1024) . ' KB');
                $chunkCount++;
                error_log("Processed chunk #$chunkCount. Memory usage: " . (memory_get_usage(true) / 1024) . ' KB');
                unset($currentChunk);
                gc_collect_cycles(); // Force garbage collection
                $currentChunk = [];  // Reset chunk
            }
            
        }
    
        // Send any remaining rows that didn't complete a full chunk
        if (!empty($currentChunk)) {
            $sendData($currentChunk);
            $chunkCount++;
            error_log("Processed final chunk #$chunkCount. Memory usage: " . (memory_get_usage(true) / 1024) . ' KB');
            unset($currentChunk);
            gc_collect_cycles(); // Force garbage collection
        }
    
        $et = microtime(true);
        $diff = $et - $st;
        $times['xlsx'][] = $diff;
        $memory['xlsx']['max_memory'] = memory_get_peak_usage(true);
    
        var_dump($times);
        var_dump($memory);
    
        error_log('Final memory usage: ' . (memory_get_usage(true) / 1024) . ' KB');
        error_log('Peak memory usage: ' . (memory_get_peak_usage(true) / 1024) . ' KB');
    }
    
}
