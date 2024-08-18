<?php

namespace OpenCartImporter\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    const CREATED_AT = 'date_added';
    const UPDATED_AT = 'date_modified';

    protected $table = 'product';
    protected $primaryKey = 'product_id';
    public $incrementing = true;

    protected $fillable = [
        'model', 'sku', 'upc', 'ean', 'jan', 'isbn', 'mpn', 'location', 
        'quantity', 'stock_status_id', 'image', 'manufacturer_id', 'shipping', 
        'price', 'tax_class_id', 'date_available', 'weight', 'weight_class_id', 'length', 
        'width', 'height', 'length_class_id', 'subtract', 'minimum', 'sort_order', 'status', 'viewed'
    ];

    public function descriptions()
    {
        return $this->hasMany(ProductDescription::class, 'product_id', 'product_id');
    }

    public function categories()
    {
        return $this->belongsToMany(ProductToCategory::class, 'product_to_category', 'product_id');
    }

    public function attributes()
    {
        return $this->hasMany(ProductAttribute::class, 'product_id', 'product_id');
    }

    public function filters(){
        return $this->hasMany(ProductFilter::class, 'product_id', 'product_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'product_id');
    }

    public function related()
    {
        return $this->hasMany(ProductRelated::class, 'related_id', 'product_id');
    }

    public function relatedCart()
    {
        return $this->hasMany(ProductRelatedCart::class, 'related_id', 'product_id');
    }

    public function discounts()
    {
        return $this->hasMany(ProductDiscount::class);
    }

    public function specials()
    {
        return $this->hasMany(ProductSpecial::class);
    }

    public function toStore()
    {
        return $this->hasMany(ProductToStore::class, 'product_id', 'product_id');
    }

    public function toLayout()
    {
        return $this->hasMany(ProductToLayout::class, 'product_id', 'product_id');
    }
}
