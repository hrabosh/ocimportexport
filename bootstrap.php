<?php
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;

// Autoload dependencies
require __DIR__ . '/vendor/autoload.php';

// Configure the database connection
$capsule = new Capsule;

// Set up database connection
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'rlx-oc-hudebniny',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => 'hudebni_knihkupectvi_',
]);

// Make this Capsule instance available globally via static methods
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Optionally set up Laravel Facade
Facade::setFacadeApplication(Container::getInstance());