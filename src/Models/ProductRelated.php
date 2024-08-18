<?php

namespace OpenCartImporter\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductRelated extends Model
{
    use HasFactory;

    // Define the table name
    protected $table = 'product_related';

    // Specify the primary key if it's not 'id'
    protected $primaryKey = 'related_id';

    // Define the fillable properties
    protected $fillable = [
        'product_id',
        'related_id'
    ];

    // Define relationships if necessary
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function relatedProduct()
    {
        return $this->belongsTo(Product::class, 'related_id');
    }
    
}
