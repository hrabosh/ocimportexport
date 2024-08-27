<?php

namespace OpenCartImporter\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDescription extends Model
{
    use HasFactory;

    protected $table = 'product_description';
    protected $primaryKey = 'product_id';

    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        'product_id',
        'language_id',
        'name',
        'description',
        'meta_title',
        'meta_description',
        'meta_keyword'    
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}

