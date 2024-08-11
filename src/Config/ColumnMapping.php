<?php

namespace OpenCartImporter\Config;

class ColumnMapping {
    public static function getMapping(): array {
        return [
            'kód' => 'model',
            'název' => 'name',
            'popis' => 'description',
            'cena' => 'price',
            'active' => 'status',
            'image' => 'image',
            'id_dostupnost' => 'availability',
            // Add other mappings as necessary
        ];
    }
}

