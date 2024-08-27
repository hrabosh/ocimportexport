#!/usr/bin/env php
<?php

require __DIR__ . '/../bootstrap.php';

use Symfony\Component\Console\Application;
use OpenCartImporter\Console\ExportProductCommand;

$application = new Application();
$application->add(new ExportProductCommand());
$application->run();
