<?php

require __DIR__ . '/../bootstrap.php';

use Symfony\Component\Console\Application;
use OpenCartImporter\Console\ImportProductCommand;

$application = new Application();
$application->add(new ImportProductCommand());
$application->run();
