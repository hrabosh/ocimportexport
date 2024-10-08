<?php

namespace OpenCartImporter\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $table = 'product_image'; // Table name
    protected $primaryKey = 'product_image_id'; // Primary key

    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = [
        'product_id',
        'image',
        'sort_order'
    ];

    // Define relationship
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
