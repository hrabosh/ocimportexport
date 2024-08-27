<?php
namespace OpenCartImporter\Logger;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class Logger {
    private static $instance = null;
    private $logger;

    public function __construct() {
        $formatter = new LineFormatter(
            "[%datetime%] %level_name%: %message% %context%\n",
            "Y-m-d H:i:s"
        );

        $handler = new StreamHandler(__DIR__ . '../../../logs/OcImportExport.log', MonologLogger::DEBUG);
        $handler->setFormatter($formatter);


        $this->logger = new MonologLogger('OcImportExport');
        $this->logger->pushHandler($handler);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Logger();
        }

        return self::$instance;
    }

    public function setType($name){

    }

    public function getLogger() {
        return $this->logger;
    }

    public function logExecutionTime(string $operation, float $startTime, float $endTime)
    {
        $executionTime = $endTime - $startTime;
        $this->logger->info("$operation completed in " . number_format($executionTime, 4) . " seconds");
    }

    public function logError(string $message, array $context = [])
    {
        $this->logger->error($message, $context);
    }

    public function logInfo(string $message, array $context = [])
    {
        $this->logger->info($message, $context);
    }
}