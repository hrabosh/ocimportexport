<?php

namespace OpenCartImporter\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSpecial extends Model
{
    use HasFactory;
    protected $table = DB_PREFIX . 'product_special';
    protected $primaryKey = 'product_special_id'; // Primary key

    public $incrementing = false;
    protected $fillable = [
        'product_id',
        'customer_group_id',
        'priority',
        'price',
        'date_start',
        'date_end'
    ];

    // Define relationship
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
