<?php

namespace OpenCartImporter\Services;

use Maatwebsite\Excel\Concerns\FromCollection;

class ProductExport implements FromCollection
{
    public function collection()
    {
        return OpenCartImporter\Models\Product::with(['descriptions', 'categories'])->get();
    }
}