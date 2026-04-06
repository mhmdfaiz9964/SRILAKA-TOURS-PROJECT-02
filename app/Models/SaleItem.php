<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'description',
        'quantity',
        'unit_price',
        'discount_percentage',
        'total_price',
        'original_item_id',
    ];

    public function originalItem()
    {
        return $this->belongsTo(SaleItem::class, 'original_item_id');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
