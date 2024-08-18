<?php

namespace OpenCartImporter\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductToCategory extends Model
{
    use HasFactory;

    protected $table = 'product_to_category';
    protected $primaryKey = 'product_id'; // Primary key

    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        'product_id',
        'category_id'
    ];

    // Define relationship
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
