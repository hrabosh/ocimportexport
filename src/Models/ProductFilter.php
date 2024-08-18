<?php

namespace OpenCartImporter\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductFilter extends Model
{
    use HasFactory;

    protected $table = 'product_filter'; // Table name
    protected $primaryKey = 'product_id'; // Primary key

    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        'product_id',
        'filter_id'
    ];

    // Define relationship
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
