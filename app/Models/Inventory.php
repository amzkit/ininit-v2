<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'store_id',
        'product_id',
        'inventory',
    ];

    public static function getInventoryFromSKU($sku, $store_id)
    {
        $product = Product::where([
            ['store_id', '=', $store_id],
            ['sku','=',$sku],
        ])->first();
        return $product->inventory??0;
    }

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public static function getInventoryArray($month){
        $inventory = [];

    }
}
