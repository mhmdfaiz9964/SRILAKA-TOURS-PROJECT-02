<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'code',
        'units',
        'stock_alert',
        'cost_price',
        'sale_price',
        'category_id',
        'is_main_product',
        'parent_product_id',
    ];

    protected $casts = [
        'is_main_product' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function parentProduct()
    {
        return $this->belongsTo(Product::class, 'parent_product_id');
    }

    public function subProducts()
    {
        return $this->hasMany(Product::class, 'parent_product_id');
    }
}
