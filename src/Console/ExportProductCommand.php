<?php

namespace OpenCartImporter\Console;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use OpenCartImporter\Services\ProductExport;

class ExportProductCommand extends Command
{
    protected $signature = 'products:import-update {filepath}';
    protected $description = 'Import and update products from an Excel file';

    public function handle()
    {
        $filePath = $this->argument('filepath');
        Excel::import(new ProductExport(), $filePath);
        $this->info('Products imported and updated successfully.');
    }
}
