<?php
return [
    'mappings' => [
        'oc_product' => [
                //'id' => 'product_id',
                //'název' => 'name',
                'cena' => 'price',
                'kód' => 'model',
                'id tax' => 'tax_class_id',
                'id_dostupnost' => 'stock_status_id',
                'active' => 'status',
                'image' => 'image',
                'ean'   => 'ean',
                'pocet' => 'quantity',
                'ISBN' => 'isbn',
                // Add other mappings for the oc_product table
        ],
        'oc_product_description' => [
                'kód' => 'model',
                'název' => 'name',
                'popis' => 'description',
                //'lang' => 'language_id', //it's defined in product
                // Add other mappings for the oc_product_description table
        ],
        'oc_product_to_category' => [
                'id_typ' => 'category_id',
                // Add other mappings for the oc_product_to_category table
        ],
        'oc_product_attribute' => [
            'lang' => 'language_id',
            'Aranžmá' => 'attribute_1',
            'Nakladatel' => 'attribute_5',
            'Formát' => 'attribute_2',
            'Hudební žánr' => 'attribute_3',
            'Médium' => 'attribute_4',
            'Náročnost' => 'attribute_6',
            'Počet stran' => 'attribute_7',
            'Jazyk' => 'attribute_8',
            'Původní popisek' => 'attribute_14',
            'DOSTUPNOST' => 'attribute_15',
            'Autorství' => 'attribute_16',
            'ean' => 'attribute_17',
            'ISBN' => 'attribute_18',
            'ISMN' => 'attribute_19',
            'Katalogové číslo' => 'attribute_20',
            'Kód aranžmá' => 'attribute_22'

            // Add other mappings for the oc_product_to_category table
        ],
        'oc_product_filter' => [
            'Nástroj 1' => 'filter_id'
            // Add other mappings for the oc_product_to_category table
        ],
        'product_discount' => [
            'customer_group_id' => 'customer_group_id'
        ]
    ]
];