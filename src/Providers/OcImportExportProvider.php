<?php

namespace OpenCartImporter\Providers;

class OcImportExportProvider
{
    public function boot()
    {
        // Register the console commands
        $this->commands([
            OpencartImporter\Console\ImportAndUpdateProducts::class,
            OpencartImporter\Console\ExportProducts::class,
        ]);

        // Publish configuration file
        $this->publishes([
            __DIR__ . '/../Config/product_import.php' => __DIR__ . '/../../Config/product_import.php',
        ]);
    }

    public function register()
    {
        // Register any package services here
    }
}