<?php

namespace OpenCartImporter\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

Class ProductToStore extends Model
{
    use HasFactory;

    protected $table = 'product_to_store';
    protected $primaryKey = 'product_id';

    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        'product_id',
        'store_id'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}