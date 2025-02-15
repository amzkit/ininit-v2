<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Vinkla\Hashids\Facades\Hashids;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'uid',
        'user_id',
        'name',
        'mobile_number',
        'address',
        'options',
    ];

    protected $casts = [
        'address' => 'array',
        'options' => 'array',
    ];

    protected $appends = [
        'owner',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'store_id', 'id');
    }

    public function salepages()
    {
        return $this->hasMany(Salepage::class);
    }

    public function payment_channels()
    {
        return $this->hasMany(PaymentChannel::class);
    }

    public function adminstores()
    {
        return $this->hasMany(AdminStore::class);
    }

    public static function isUIDValid($store_id, $uid)
    {
        $store = Store::where([
                ['id',"!=",$store_id],
                ['uid', '=', $uid],
            ])->first();

        if ($store) {
            return false;
        }

        return true;

    }

    public function getOwnerAttribute(){
        $request_user_id = auth()->user()->id??false;
        $store_user_id = $this->user_id;

        //dd($request_user_id, $store_user_id);
        if($request_user_id == $store_user_id){
            return true;
        }

        return false;
    }

    public function getProductFromAlias($alias)
    {
        $aliases = Alias::where([
            ['alias', '=', $alias]
        ])->get();

        foreach ($aliases as $alias) {

            $product = Product::find($alias->product_id);
            $store_id = $product->store_id;
            if ($store_id == $this->id) {
                return $product;
            }
        }
        return false;
    }

    static public function getStoreFromUID($uid)
    {
        $store = Store::where('uid', $uid)->first();
        return $store;
    }

    public function getFlashapiAttribute(){
        $flashapi_merchantID = $this->options['flashapi_merchantID']??null;
        $flashapi_merchantPW = $this->options['flashapi_merchantPW']??null;
        if($flashapi_merchantID != null && $flashapi_merchantPW != null){
            return [
                'merchantID'    =>  $flashapi_merchantID,
                'merchantPW'    =>  $flashapi_merchantPW
            ];
        }
        return null;
    }
}
