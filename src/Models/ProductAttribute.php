<?php

namespace OpenCartImporter\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    use HasFactory;

    protected $table = 'product_attribute'; // Table name
    protected $primaryKey = 'product_attribute_id'; // Primary key

    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = [
        'product_id',
        'attribute_id',
        'language_id',
        'text'
    ];

    // Define relationship
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
