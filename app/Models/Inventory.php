<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'store_id',
        'product_id',
        'inventory',
    ];

    public static function getInventoryFromSKU($sku, $store_id, $date = null)
    {
        // Find the product first
        $product = Product::where([
            ['store_id', '=', $store_id],
            ['sku', '=', $sku],
        ])->first();
    
        if (!$product) {
            return 0; // Return 0 if product doesn't exist
        }
    
        // Query inventory for the specific date (or latest available before the date)
        $inventory = Inventory::where([
            ['store_id', '=', $store_id],
            ['product_id', '=', $product->id],
        ])
        ->whereDate('created_at', '<=', $date ?? now()) // Get latest before or on the date
        ->latest('created_at') // Ensure we get the most recent entry before or on the date
        ->first();
    
        return $inventory->inventory ?? 0;
    }
    


    public static function getLastInventoryFromSKU($sku, $store_id, $date = null)
    {
        // If no date is provided, use yesterday's date
        $date = $date ? Carbon::parse($date)->format('Y-m-d') : Carbon::now()->subDay()->format('Y-m-d');

        // Get the product
        $product = Product::where([
            ['store_id', '=', $store_id],
            ['sku', '=', $sku],
        ])->first();

        if (!$product) {
            return 0; // Product not found
        }

        // Loop to find the last available inventory before the given date (up to 30 days back)
        for ($i = 0; $i < 30; $i++) {
            $inventory = Inventory::where([
                ['store_id', '=', $store_id],
                ['product_id', '=', $product->id],
            ])
            ->whereDate('created_at', '<=', $date)
            ->latest('created_at') // Get the most recent record before or on the date
            ->first();
            dd($date, $inventory);
            if ($inventory) {
                return $inventory->inventory;
            }

            // Move one day back
            $date = Carbon::parse($date)->subDay()->format('Y-m-d');
        }

        return 0; // No inventory found within 30 days
    }


    public function product(){
        return $this->belongsTo(Product::class);
    }

}
