<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;
use Vinkla\Hashids\Facades\Hashids;

class Product extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'id',
        'store_id',
        'product_id',
        'sku',
        'image',
        'aliases',
        'name',
        'full_price',
        'price',
        'dropship_price',
        'description',
        'options',
    ];

    protected $casts = [
        'aliases'   =>  'array',
        'options'   =>  'array',
    ];

    protected $appends = [
        'slug',
        'inventory',
    ];

    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function getSlugAttribute()
    {
        return Hashids::connection('product')->encode($this->store_id, $this->product_id);
    }

    public static function findBySKU($store_id, $sku)
    {
        $product = Product::where([
            ['store_id' ,   '=' ,   $store_id],
            ['sku'      ,   '=' ,   $sku]
        ])->first();

        if(!$product){
            // find in aliase
            $product = Product::where([
                ['store_id' ,   '='     ,   $store_id],
                ['aliases'  ,   'like'  ,   '%'.$sku.'%'],
            ])->first();
        }

        return $product;
    }

    public static function findBySlug($slug)
    {
        $ids = Hashids::connection('product')->decode($slug);
        if(!$ids){
            // not found
            return null;
        }
        $store_id = $ids[0];
        $store_product_id = $ids[1];

        $product = Product::where([
            ['store_id', '=', $store_id],
            ['product_id', '=', $store_product_id]
        ])->first();
        return $product;
    }

    public function categories()
    {
        return $this->hasMany(ProductCategory::class, 'product_id');
    }

    //    public function aliases()
    //    {
    //        return $this->hasMany(Alias::class, 'product_id');
    //    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class)
                    ->whereDate('created_at', \Carbon\Carbon::today());
    }

    public function getInventoryAttribute()
    {
        $result = 0;

        $inventory = Inventory::where([
            ['product_id','=',$this->id],
        ])->orderBy('updated_at', 'DESC')->first();
        
        if(isset($inventory))
            $result = $inventory->inventory;
        
        return $result;
    }
    /*
    public function getSkuAttribute()
    {
        return $this->aliases[0]??'';
    }
*/
    // For Product Object (without itself)
    public function isAliasValid($alias)
    {
        $product = Product::where([
            ['store_id', '=', $this->store->id],
            ['id', '!=', $this->id],
            ['sku', '=',  $alias],
        ])
            ->first();
        if ($product) {
            return false;
        }
/*      For MySQL 10.2.3+
        $product = Product::where([
            ['store_id', '=', $this->store->id],
            ['id', '!=', $this->id]
        ])
            ->whereRaw('json_contains(aliases, \'["' . $alias . '"]\')')->first();
*/
        $product = Product::where([
            ['store_id', '=', $this->store->id],
            ['id', '!=', $this->id],
            ['aliases', 'LIKE', '%\"'.$alias.'\"%']
        ])->first();

        if ($product) {
            return false;
        }

        return true;
    }

    // For Non Object, Check whole database with user's store
    public static function isAliasExisted($store_id, $alias)
    {
        $product = Product::where([
            ['store_id', '=', $store_id],
            //['id', '!=', $this->id],
            ['sku', '=',  $alias],
        ])
            ->first();
        if ($product) {
            return true;
        }

        $product = Product::where([
            ['store_id', '=', $store_id],
            //    ['id', '!=', $this->id]
        ])
            ->whereRaw(
                'json_contains(aliases, \'["' . $alias . '"]\')'
            )->first();
        if ($product) {
            return true;
        }

        return false;
    }
}
